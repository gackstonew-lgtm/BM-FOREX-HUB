import React, { useState } from 'react';
import { UserSummary } from '../../types/user-management';
import { SuspendUserModal } from './SuspendUserModal';
import { DeleteUserModal } from './DeleteUserModal';

interface UserActionsDropdownProps {
  user: UserSummary;
  onRefresh: () => void;
}

export const UserActionsDropdown: React.FC<UserActionsDropdownProps> = ({ user, onRefresh }) => {
  const [isOpen, setIsOpen] = useState(false);
  const [showSuspendModal, setShowSuspendModal] = useState(false);
  const [showDeleteModal, setShowDeleteModal] = useState(false);

  const toggleDropdown = () => setIsOpen(!isOpen);

  const handleOpenSuspend = () => {
    setIsOpen(false);
    setShowSuspendModal(true);
  };

  const handleOpenDelete = () => {
    setIsOpen(false);
    setShowDeleteModal(true);
  };

  const isSuspended = user.status === 'SUSPENDED';
  const isDeleted = user.status === 'DELETED';

  return (
    <div className="relative inline-block text-left">
      {/* Dropdown Trigger */}
      <button
        type="button"
        onClick={toggleDropdown}
        className="p-1.5 rounded-lg text-slate-400 hover:text-slate-200 hover:bg-slate-800 transition-colors focus:outline-none focus:ring-2 focus:ring-slate-700"
        aria-label="User actions"
      >
        <svg className="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z" />
        </svg>
      </button>

      {/* Dropdown Menu */}
      {isOpen && (
        <>
          {/* Backdrop to close menu */}
          <div className="fixed inset-0 z-10" onClick={() => setIsOpen(false)} />

          <div className="absolute right-0 z-20 mt-1 w-48 rounded-lg bg-slate-900 border border-slate-800 shadow-xl py-1 text-xs text-slate-200 divide-y divide-slate-800/60 animate-in fade-in zoom-in-95 duration-150">
            <div className="px-3 py-2 text-[11px] font-semibold text-slate-400 uppercase tracking-wider">
              Admin Actions
            </div>

            <div className="py-1">
              {/* Suspend Action */}
              {!isDeleted && (
                <button
                  type="button"
                  onClick={handleOpenSuspend}
                  disabled={isSuspended}
                  className="w-full text-left px-3 py-2 text-amber-400 hover:bg-amber-500/10 flex items-center space-x-2 transition-colors disabled:opacity-40 disabled:cursor-not-allowed"
                >
                  <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                  </svg>
                  <span>{isSuspended ? 'Account Suspended' : 'Suspend Account'}</span>
                </button>
              )}

              {/* Delete Action */}
              {!isDeleted && (
                <button
                  type="button"
                  onClick={handleOpenDelete}
                  className="w-full text-left px-3 py-2 text-rose-400 hover:bg-rose-500/10 flex items-center space-x-2 transition-colors"
                >
                  <svg className="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                  </svg>
                  <span>Delete Account (GDPR)</span>
                </button>
              )}

              {isDeleted && (
                <div className="px-3 py-2 text-slate-500 italic">
                  Account Soft-Deleted
                </div>
              )}
            </div>
          </div>
        </>
      )}

      {/* Modals */}
      <SuspendUserModal
        isOpen={showSuspendModal}
        onClose={() => setShowSuspendModal(false)}
        user={user}
        onSuccess={onRefresh}
      />

      <DeleteUserModal
        isOpen={showDeleteModal}
        onClose={() => setShowDeleteModal(false)}
        user={user}
        onSuccess={onRefresh}
      />
    </div>
  );
};
