export type UserRole = 'USER' | 'ROLE_ADMIN' | 'ROLE_SUPER_ADMIN';

export type UserStatus = 'ACTIVE' | 'SUSPENDED' | 'DELETED';

export interface UserSummary {
  id: string;
  email: string;
  firstName?: string | null;
  lastName?: string | null;
  phone?: string | null;
  role: UserRole;
  status: UserStatus;
  createdAt: string;
  suspendedAt?: string | null;
  suspensionReason?: string | null;
  deletedAt?: string | null;
}

export interface SuspendUserPayload {
  reason: string;
}

export interface DeleteUserPayload {
  reason: string;
  confirmation: string;
}

export interface ApiResponse<T = any> {
  success: boolean;
  message?: string;
  data?: T;
  error?: {
    code: string;
    message: string;
    details?: Record<string, string[]>;
  };
}
