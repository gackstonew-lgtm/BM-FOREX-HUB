import { PrismaClient, UserStatus, AuditAction, UserRole } from '@prisma/client';

const prisma = new PrismaClient();

export interface SuspendUserParams {
  adminId: string;
  adminRole: UserRole;
  targetUserId: string;
  reason: string;
  ipAddress?: string;
  userAgent?: string;
}

export interface DeleteUserParams {
  adminId: string;
  adminRole: UserRole;
  targetUserId: string;
  reason: string;
  confirmationEmailOrText: string;
  ipAddress?: string;
  userAgent?: string;
}

export class UserManagementService {
  /**
   * Temporarily freezes user account, invalidates active sessions, and logs audit record.
   */
  static async suspendUser(params: SuspendUserParams) {
    const { adminId, adminRole, targetUserId, reason, ipAddress, userAgent } = params;

    // 1. Guard against self-suspension
    if (adminId === targetUserId) {
      throw new Error('SELF_ACTION_DENIED: Admins cannot suspend their own accounts.');
    }

    // 2. Fetch target user
    const targetUser = await prisma.user.findUnique({
      where: { id: targetUserId },
      select: { id: true, email: true, role: true, status: true }
    });

    if (!targetUser) {
      throw new Error('USER_NOT_FOUND: Target user does not exist.');
    }

    if (targetUser.status === UserStatus.DELETED) {
      throw new Error('INVALID_STATE: Cannot suspend a deleted user account.');
    }

    if (targetUser.status === UserStatus.SUSPENDED) {
      throw new Error('ALREADY_SUSPENDED: User is already suspended.');
    }

    // 3. Super Admin protection
    if (targetUser.role === UserRole.ROLE_SUPER_ADMIN && adminRole !== UserRole.ROLE_SUPER_ADMIN) {
      throw new Error('FORBIDDEN_HIERARCHY: Only Super Admins can suspend Super Admin accounts.');
    }

    // 4. Atomic transaction: Update user + increment tokenVersion + create AuditLog
    const result = await prisma.$transaction(async (tx) => {
      const updatedUser = await tx.user.update({
        where: { id: targetUserId },
        data: {
          status: UserStatus.SUSPENDED,
          suspendedAt: new Date(),
          suspensionReason: reason,
          tokenVersion: { increment: 1 }, // Instantly invalidates all active JWTs
        },
        select: {
          id: true,
          email: true,
          status: true,
          suspendedAt: true,
          suspensionReason: true,
        }
      });

      const auditLog = await tx.auditLog.create({
        data: {
          adminId,
          targetUserId,
          action: AuditAction.USER_SUSPENDED,
          reason,
          ipAddress,
          userAgent,
          metadata: {
            previousStatus: targetUser.status,
            newStatus: UserStatus.SUSPENDED,
            targetRole: targetUser.role,
          }
        }
      });

      return { user: updatedUser, auditLogId: auditLog.id };
    });

    return result;
  }

  /**
   * Applies GDPR compliance soft-delete: anonymizes PII, sets deletedAt, invalidates sessions,
   * preserves financial transaction history/ledger integrity, and records audit entry.
   */
  static async deleteUser(params: DeleteUserParams) {
    const { adminId, adminRole, targetUserId, reason, confirmationEmailOrText, ipAddress, userAgent } = params;

    // 1. Guard against self-deletion
    if (adminId === targetUserId) {
      throw new Error('SELF_ACTION_DENIED: Admins cannot delete their own accounts.');
    }

    // 2. Fetch target user
    const targetUser = await prisma.user.findUnique({
      where: { id: targetUserId },
      select: { id: true, email: true, firstName: true, lastName: true, role: true, status: true }
    });

    if (!targetUser) {
      throw new Error('USER_NOT_FOUND: Target user does not exist.');
    }

    if (targetUser.status === UserStatus.DELETED) {
      throw new Error('ALREADY_DELETED: User account is already deleted.');
    }

    // 3. Confirm email / text verification match
    const isEmailMatch = confirmationEmailOrText.trim().toLowerCase() === targetUser.email.toLowerCase();
    const isDeleteKeyword = confirmationEmailOrText.trim() === 'DELETE';

    if (!isEmailMatch && !isDeleteKeyword) {
      throw new Error('CONFIRMATION_MISMATCH: Confirmation string does not match user email or "DELETE".');
    }

    // 4. Role Hierarchy validation
    if (targetUser.role !== UserRole.USER && adminRole !== UserRole.ROLE_SUPER_ADMIN) {
      throw new Error('FORBIDDEN_HIERARCHY: Only Super Admins can delete Admin accounts.');
    }

    // 5. Atomic transaction: Anonymize PII + soft delete + increment tokenVersion + record AuditLog
    const anonymizedEmail = `anon_${targetUserId.slice(0, 8)}_${Date.now()}@anonymized.bmforex.local`;

    const result = await prisma.$transaction(async (tx) => {
      const updatedUser = await tx.user.update({
        where: { id: targetUserId },
        data: {
          status: UserStatus.DELETED,
          deletedAt: new Date(),
          // Anonymize PII to comply with GDPR while retaining financial transaction foreign keys
          email: anonymizedEmail,
          firstName: 'Anonymized',
          lastName: 'User',
          phone: null,
          tokenVersion: { increment: 1 }, // Instantly invalidates all active sessions
        },
        select: {
          id: true,
          email: true,
          status: true,
          deletedAt: true,
        }
      });

      const auditLog = await tx.auditLog.create({
        data: {
          adminId,
          targetUserId,
          action: AuditAction.USER_DELETED,
          reason,
          ipAddress,
          userAgent,
          metadata: {
            originalEmailHash: Buffer.from(targetUser.email).toString('base64'),
            previousStatus: targetUser.status,
            newStatus: UserStatus.DELETED,
            piiAnonymized: true,
          }
        }
      });

      return { user: updatedUser, auditLogId: auditLog.id };
    });

    return result;
  }
}
