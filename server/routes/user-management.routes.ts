import { Router } from 'express';
import { requireAdmin } from '../middleware/rbac.middleware';
import { UserManagementController } from '../controllers/user-management.controller';

const router = Router();

/**
 * @route   POST /api/v1/admin/users/:id/suspend
 * @desc    Freeze user account, invalidate all active tokens, and write audit log
 * @access  Private (ROLE_ADMIN, ROLE_SUPER_ADMIN)
 */
router.post('/users/:id/suspend', ...requireAdmin, UserManagementController.suspendUser);

/**
 * @route   POST /api/v1/admin/users/:id/delete
 * @desc    GDPR Soft-Delete user account, anonymize PII, retain transaction ledger, write audit log
 * @access  Private (ROLE_ADMIN, ROLE_SUPER_ADMIN)
 */
router.post('/users/:id/delete', ...requireAdmin, UserManagementController.deleteUser);

export default router;
