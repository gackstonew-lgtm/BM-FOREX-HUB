import { Request, Response, NextFunction } from 'express';
import jwt from 'jsonwebtoken';
import { PrismaClient, UserRole, UserStatus } from '@prisma/client';

const prisma = new PrismaClient();

export interface AuthenticatedUser {
  id: string;
  email: string;
  role: UserRole;
  tokenVersion: number;
}

export interface AuthenticatedRequest extends Request {
  user?: AuthenticatedUser;
}

interface JwtPayload {
  userId: string;
  email: string;
  role: UserRole;
  tokenVersion: number;
}

/**
 * Authenticate JWT middleware with live tokenVersion check.
 * If user tokenVersion has been incremented (due to suspension/deletion), token is rejected.
 */
export const authenticateJwt = async (
  req: AuthenticatedRequest,
  res: Response,
  next: NextFunction
): Promise<void> => {
  try {
    const authHeader = req.headers.authorization;
    if (!authHeader || !authHeader.startsWith('Bearer ')) {
      res.status(401).json({
        success: false,
        error: { code: 'UNAUTHORIZED', message: 'Authentication token missing or invalid' }
      });
      return;
    }

    const token = authHeader.split(' ')[1];
    const secret = process.env.JWT_SECRET || 'fallback-secret-for-dev';
    const decoded = jwt.verify(token, secret) as JwtPayload;

    // Fetch user from DB to check status and tokenVersion validity
    const user = await prisma.user.findUnique({
      where: { id: decoded.userId },
      select: {
        id: true,
        email: true,
        role: true,
        status: true,
        tokenVersion: true,
      }
    });

    if (!user) {
      res.status(401).json({
        success: false,
        error: { code: 'USER_NOT_FOUND', message: 'User account no longer exists' }
      });
      return;
    }

    // Check if account is suspended or soft-deleted
    if (user.status === UserStatus.SUSPENDED) {
      res.status(403).json({
        success: false,
        error: { code: 'ACCOUNT_SUSPENDED', message: 'Your account has been suspended. Please contact support.' }
      });
      return;
    }

    if (user.status === UserStatus.DELETED) {
      res.status(401).json({
        success: false,
        error: { code: 'ACCOUNT_DELETED', message: 'This account has been deactivated.' }
      });
      return;
    }

    // Live session revocation check: if tokenVersion changed, reject JWT
    if (user.tokenVersion !== decoded.tokenVersion) {
      res.status(401).json({
        success: false,
        error: { code: 'SESSION_REVOKED', message: 'Session has been invalidated. Please log in again.' }
      });
      return;
    }

    req.user = {
      id: user.id,
      email: user.email,
      role: user.role,
      tokenVersion: user.tokenVersion,
    };

    next();
  } catch (error) {
    res.status(401).json({
      success: false,
      error: { code: 'INVALID_TOKEN', message: 'Invalid or expired authentication token' }
    });
  }
};

/**
 * Role-Based Access Control (RBAC) middleware generator.
 * Restricts access to specific UserRole(s).
 */
export const requireRoles = (allowedRoles: UserRole[]) => {
  return (req: AuthenticatedRequest, res: Response, next: NextFunction): void => {
    if (!req.user) {
      res.status(401).json({
        success: false,
        error: { code: 'UNAUTHORIZED', message: 'User not authenticated' }
      });
      return;
    }

    if (!allowedRoles.includes(req.user.role)) {
      res.status(403).json({
        success: false,
        error: {
          code: 'FORBIDDEN',
          message: 'Access denied. You do not possess the required admin permissions.'
        }
      });
      return;
    }

    next();
  };
};

export const requireAdmin = [
  authenticateJwt,
  requireRoles([UserRole.ROLE_ADMIN, UserRole.ROLE_SUPER_ADMIN])
];
