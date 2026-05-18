"use client";

import { motion } from 'framer-motion';
import { Users, FileText, Calendar, TrendingUp } from 'lucide-react';

export default function HRDashboard() {
  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
      <div style={{ marginBottom: '2rem' }}>
        <h1 style={{ color: 'var(--primary)', marginBottom: '0.5rem' }}>HR Dashboard</h1>
        <p style={{ color: 'var(--secondary)' }}>Manage employee lifecycle, leaves, and organizational performance.</p>
      </div>

      <div style={{ display: 'grid', gridTemplateColumns: 'repeat(auto-fit, minmax(280px, 1fr))', gap: '1.5rem', marginBottom: '2rem' }}>
        {/* Module Cards */}
        <div className="premium-card" style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div style={{ width: '40px', height: '40px', background: 'rgba(59, 130, 246, 0.1)', color: 'var(--primary)', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Users size={20} />
          </div>
          <h3 style={{ fontSize: '1.25rem' }}>Employee Records</h3>
          <p style={{ color: 'var(--secondary)', fontSize: '0.875rem' }}>Manage staff bio-data, variations, education, and service records.</p>
          <button className="premium-btn" style={{ background: 'var(--surface-hover)', color: 'var(--foreground)', marginTop: 'auto' }}>Open Module</button>
        </div>

        <div className="premium-card" style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div style={{ width: '40px', height: '40px', background: 'rgba(16, 185, 129, 0.1)', color: '#10b981', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <Calendar size={20} />
          </div>
          <h3 style={{ fontSize: '1.25rem' }}>Leave Management</h3>
          <p style={{ color: 'var(--secondary)', fontSize: '0.875rem' }}>Process annual leaves, leave of absence, and tour records.</p>
          <button className="premium-btn" style={{ background: 'var(--surface-hover)', color: 'var(--foreground)', marginTop: 'auto' }}>Open Module</button>
        </div>

        <div className="premium-card" style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div style={{ width: '40px', height: '40px', background: 'rgba(245, 158, 11, 0.1)', color: '#f59e0b', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <TrendingUp size={20} />
          </div>
          <h3 style={{ fontSize: '1.25rem' }}>Performance & Promotions</h3>
          <p style={{ color: 'var(--secondary)', fontSize: '0.875rem' }}>Track censures, commendations, and handle promotion briefs.</p>
          <button className="premium-btn" style={{ background: 'var(--surface-hover)', color: 'var(--foreground)', marginTop: 'auto' }}>Open Module</button>
        </div>

        <div className="premium-card" style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
          <div style={{ width: '40px', height: '40px', background: 'rgba(139, 92, 246, 0.1)', color: '#8b5cf6', borderRadius: '10px', display: 'flex', alignItems: 'center', justifyContent: 'center' }}>
            <FileText size={20} />
          </div>
          <h3 style={{ fontSize: '1.25rem' }}>Pension & Gratuity</h3>
          <p style={{ color: 'var(--secondary)', fontSize: '0.875rem' }}>Process retirements, pensions, and final entitlements.</p>
          <button className="premium-btn" style={{ background: 'var(--surface-hover)', color: 'var(--foreground)', marginTop: 'auto' }}>Open Module</button>
        </div>
      </div>
    </motion.div>
  );
}
