import { Response } from 'express';
import { z } from 'zod';
import { AuthenticatedRequest } from '../middleware/rbac.middleware';
import { UserManagementService } from '../services/user-management.service';

// Validation Schemas using Zod
export const suspendUserSchema = z.object({
  reason: z.string().min(5, 'Suspension reason must be at least 5 characters long.').max(1000, 'Reason too long.'),
});

export const deleteUserSchema = z.object({
  reason: z.string().min(5, 'Deletion reason must be at least 5 characters long.').max(1000, 'Reason too long.'),
  confirmation: z.string().min(1, 'Confirmation text or email is required.'),
});

export class UserManagementController {
  /**
   * Endpoint: POST /api/v1/admin/users/:id/suspend
   */
  static async suspendUser(req: AuthenticatedRequest, res: Response): Promise<void> {
    try {
      const targetUserId = req.params.id;
      const admin = req.user;

      if (!admin) {
        res.status(401).json({ success: false, error: { code: 'UNAUTHORIZED', message: 'Authentication required' } });
        return;
      }

      // Validate request payload
      const parseResult = suspendUserSchema.safeParse(req.body);
      if (!parseResult.success) {
        res.status(400).json({
          success: false,
          error: {
            code: 'VALIDATION_ERROR',
            message: 'Invalid request payload',
            details: parseResult.error.flatten().fieldErrors,
          }
        });
        return;
      }

      const { reason } = parseResult.data;
      const ipAddress = req.ip || (req.headers['x-forwarded-for'] as string) || undefined;
      const userAgent = req.headers['user-agent'];

      const result = await UserManagementService.suspendUser({
        adminId: admin.id,
        adminRole: admin.role,
        targetUserId,
        reason,
        ipAddress,
        userAgent,
      });

      res.status(200).json({
        success: true,
        message: 'User account suspended successfully. All active sessions invalidated.',
        data: result,
      });
    } catch (error: any) {
      const message = error.message || 'An error occurred during suspension.';
      let statusCode = 500;

      if (message.startsWith('SELF_ACTION_DENIED')) statusCode = 400;
      else if (message.startsWith('USER_NOT_FOUND')) statusCode = 404;
      else if (message.startsWith('INVALID_STATE') || message.startsWith('ALREADY_SUSPENDED')) statusCode = 409;
      else if (message.startsWith('FORBIDDEN_HIERARCHY')) statusCode = 403;

      res.status(statusCode).json({
        success: false,
        error: { code: message.split(':')[0], message: message.split(':')[1]?.trim() || message }
      });
    }
  }

  /**
   * Endpoint: POST /api/v1/admin/users/:id/delete
   */
  static async deleteUser(req: AuthenticatedRequest, res: Response): Promise<void> {
    try {
      const targetUserId = req.params.id;
      const admin = req.user;

      if (!admin) {
        res.status(401).json({ success: false, error: { code: 'UNAUTHORIZED', message: 'Authentication required' } });
        return;
      }

      // Validate request payload
      const parseResult = deleteUserSchema.safeParse(req.body);
      if (!parseResult.success) {
        res.status(400).json({
          success: false,
          error: {
            code: 'VALIDATION_ERROR',
            message: 'Invalid request payload',
            details: parseResult.error.flatten().fieldErrors,
          }
        });
        return;
      }

      const { reason, confirmation } = parseResult.data;
      const ipAddress = req.ip || (req.headers['x-forwarded-for'] as string) || undefined;
      const userAgent = req.headers['user-agent'];

      const result = await UserManagementService.deleteUser({
        adminId: admin.id,
        adminRole: admin.role,
        targetUserId,
        reason,
        confirmationEmailOrText: confirmation,
        ipAddress,
        userAgent,
      });

      res.status(200).json({
        success: true,
        message: 'User account soft-deleted and PII anonymized successfully. Transaction ledger entries retained.',
        data: result,
      });
    } catch (error: any) {
      const message = error.message || 'An error occurred during deletion.';
      let statusCode = 500;

      if (message.startsWith('SELF_ACTION_DENIED') || message.startsWith('CONFIRMATION_MISMATCH')) statusCode = 400;
      else if (message.startsWith('USER_NOT_FOUND')) statusCode = 404;
      else if (message.startsWith('ALREADY_DELETED')) statusCode = 409;
      else if (message.startsWith('FORBIDDEN_HIERARCHY')) statusCode = 403;

      res.status(statusCode).json({
        success: false,
        error: { code: message.split(':')[0], message: message.split(':')[1]?.trim() || message }
      });
    }
  }
}
