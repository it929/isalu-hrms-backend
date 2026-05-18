"use client";

import { useState } from 'react';
import { motion } from 'framer-motion';
import { Plus, Edit2, Shield, Layers } from 'lucide-react';

export default function RolesAndModules() {
  const [activeTab, setActiveTab] = useState('roles');

  return (
    <motion.div 
      initial={{ opacity: 0 }}
      animate={{ opacity: 1 }}
    >
      <div style={{ display: 'flex', justifyContent: 'space-between', alignItems: 'center', marginBottom: '2rem' }}>
        <div>
          <h1 style={{ color: 'var(--primary)', marginBottom: '0.5rem' }}>Roles & Modules</h1>
          <p style={{ color: 'var(--secondary)' }}>Manage system roles and feature module access.</p>
        </div>
      </div>

      <div style={{ display: 'flex', gap: '1rem', marginBottom: '1.5rem', borderBottom: '1px solid var(--border)', paddingBottom: '0.5rem' }}>
        <button 
          onClick={() => setActiveTab('roles')}
          style={{ background: 'none', border: 'none', color: activeTab === 'roles' ? 'var(--primary)' : 'var(--secondary)', fontWeight: '600', padding: '0.5rem 1rem', cursor: 'pointer', borderBottom: activeTab === 'roles' ? '2px solid var(--primary)' : '2px solid transparent', display: 'flex', alignItems: 'center', gap: '0.5rem' }}
        >
          <Shield size={18} /> Roles
        </button>
        <button 
          onClick={() => setActiveTab('modules')}
          style={{ background: 'none', border: 'none', color: activeTab === 'modules' ? 'var(--primary)' : 'var(--secondary)', fontWeight: '600', padding: '0.5rem 1rem', cursor: 'pointer', borderBottom: activeTab === 'modules' ? '2px solid var(--primary)' : '2px solid transparent', display: 'flex', alignItems: 'center', gap: '0.5rem' }}
        >
          <Layers size={18} /> Modules
        </button>
      </div>

      <div className="premium-card">
        <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '1.5rem' }}>
          <h3 style={{ fontSize: '1.25rem' }}>{activeTab === 'roles' ? 'System Roles' : 'System Modules'}</h3>
          <button className="premium-btn" style={{ display: 'flex', alignItems: 'center', gap: '0.5rem', padding: '0.4rem 0.8rem', fontSize: '0.875rem' }}>
            <Plus size={16} /> Add {activeTab === 'roles' ? 'Role' : 'Module'}
          </button>
        </div>

        {activeTab === 'roles' ? (
          <div style={{ display: 'grid', gap: '1rem', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))' }}>
            {['Super Admin', 'Admin', 'Salary Supervisor', 'HR Manager'].map((role, i) => (
              <div key={i} style={{ padding: '1.5rem', border: '1px solid var(--border)', borderRadius: 'var(--radius)', background: 'var(--surface)' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '1rem' }}>
                  <h4 style={{ fontWeight: '600', fontSize: '1.1rem' }}>{role}</h4>
                  <button style={{ background: 'none', border: 'none', color: 'var(--secondary)', cursor: 'pointer' }}><Edit2 size={16} /></button>
                </div>
                <p style={{ color: 'var(--secondary)', fontSize: '0.875rem', marginBottom: '1rem' }}>Has full access to all sub-modules assigned to this role.</p>
                <div style={{ fontSize: '0.875rem', fontWeight: '500', color: 'var(--primary)' }}>24 Users Assigned</div>
              </div>
            ))}
          </div>
        ) : (
          <div style={{ display: 'grid', gap: '1rem', gridTemplateColumns: 'repeat(auto-fill, minmax(300px, 1fr))' }}>
            {['HR Module', 'Payroll Module', 'Procurement', 'Funds Management'].map((mod, i) => (
              <div key={i} style={{ padding: '1.5rem', border: '1px solid var(--border)', borderRadius: 'var(--radius)', background: 'var(--surface)' }}>
                <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '1rem' }}>
                  <h4 style={{ fontWeight: '600', fontSize: '1.1rem' }}>{mod}</h4>
                  <button style={{ background: 'none', border: 'none', color: 'var(--secondary)', cursor: 'pointer' }}><Edit2 size={16} /></button>
                </div>
                <p style={{ color: 'var(--secondary)', fontSize: '0.875rem', marginBottom: '1rem' }}>Core application module.</p>
                <div style={{ fontSize: '0.875rem', fontWeight: '500', color: 'var(--primary)' }}>12 Sub-modules</div>
              </div>
            ))}
          </div>
        )}
      </div>
    </motion.div>
  );
}
