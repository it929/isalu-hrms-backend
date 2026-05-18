"use client";

import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import axios from 'axios';
import Link from 'next/link';
import { UserPlus, Search, Eye, Users, FileText } from 'lucide-react';
import styles from './page.module.css';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/nextjs';

export default function EmployeeRecords() {
  const [staffList, setStaffList] = useState([]);
  const [loading, setLoading] = useState(true);
  const [search, setSearch] = useState('');

  useEffect(() => {
    axios.get(`${API_BASE}/hr/add-staff/list`)
      .then(res => setStaffList(res.data.staff || []))
      .catch(() => setStaffList([]))
      .finally(() => setLoading(false));
  }, []);

  const filtered = staffList.filter(s => {
    const q = search.toLowerCase();
    return (
      s.surname?.toLowerCase().includes(q) ||
      s.first_name?.toLowerCase().includes(q) ||
      s.email?.toLowerCase().includes(q) ||
      s.pf_num?.toLowerCase().includes(q) ||
      s.designation?.toLowerCase().includes(q) ||
      s.department?.toLowerCase().includes(q)
    );
  });

  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
      {/* ── Header ── */}
      <div className={styles.pageHeader}>
        <div>
          <h1 className={styles.pageTitle}>Employee Records</h1>
          <p className={styles.pageSubtitle}>View and manage all administrative staff in the system.</p>
        </div>
        <Link href="/dashboard/hr/employees/add" className={styles.addBtn}>
          <UserPlus size={18} />
          <span>Add New Staff</span>
        </Link>
      </div>

      {/* ── Stats Row ── */}
      <div className={styles.statsRow}>
        <div className={`premium-card ${styles.statCard}`}>
          <div className={styles.statIcon} style={{ background: 'rgba(59,130,246,0.1)', color: 'var(--primary)' }}>
            <Users size={22} />
          </div>
          <div>
            <p className={styles.statValue}>{staffList.length}</p>
            <p className={styles.statLabel}>Total Staff</p>
          </div>
        </div>
        <div className={`premium-card ${styles.statCard}`}>
          <div className={styles.statIcon} style={{ background: 'rgba(16,185,129,0.1)', color: '#10b981' }}>
            <Users size={22} />
          </div>
          <div>
            <p className={styles.statValue}>{staffList.filter(s => s.gender === 'Male').length}</p>
            <p className={styles.statLabel}>Male Staff</p>
          </div>
        </div>
        <div className={`premium-card ${styles.statCard}`}>
          <div className={styles.statIcon} style={{ background: 'rgba(245,158,11,0.1)', color: '#f59e0b' }}>
            <Users size={22} />
          </div>
          <div>
            <p className={styles.statValue}>{staffList.filter(s => s.gender === 'Female').length}</p>
            <p className={styles.statLabel}>Female Staff</p>
          </div>
        </div>
      </div>

      {/* ── Table Card ── */}
      <div className={`premium-card ${styles.tableCard}`}>
        <div className={styles.tableHeader}>
          <h2>Staff List</h2>
          <div className={styles.searchWrap}>
            <Search size={16} className={styles.searchIcon} />
            <input
              type="text"
              placeholder="Search by name, PF No., dept…"
              value={search}
              onChange={e => setSearch(e.target.value)}
              className={styles.searchInput}
            />
          </div>
        </div>

        <div className={styles.tableScroll}>
          <table className={styles.table}>
            <thead>
              <tr>
                <th>S/N</th>
                <th>Full Name</th>
                <th>Date of Birth</th>
                <th>Gender</th>
                <th>Marital Status</th>
                <th>DATE OF APPOINTMENT</th>
                <th>Action</th>
              </tr>
            </thead>
            <tbody>
              {loading ? (
                <tr>
                  <td colSpan={7} className={styles.emptyRow}>Loading staff records…</td>
                </tr>
              ) : filtered.length === 0 ? (
                <tr>
                  <td colSpan={7} className={styles.emptyRow}>
                    {search ? 'No results match your search.' : 'No staff records found. Click "Add New Staff" to get started.'}
                  </td>
                </tr>
              ) : (
                filtered.map((s, i) => (
                  <motion.tr
                    key={i}
                    initial={{ opacity: 0, y: 8 }}
                    animate={{ opacity: 1, y: 0 }}
                    transition={{ delay: i * 0.03 }}
                    className={styles.tableRow}
                  >
                    <td>{i + 1}</td>
                    <td className={styles.nameCell}>
                      {[s.title, s.surname, s.first_name, s.othernames].filter(Boolean).join(' ')}
                    </td>
                    <td>{s.dob || '—'}</td>
                    <td>
                      <span className={`${styles.genderTag} ${s.gender === 'Female' ? styles.female : styles.male}`}>
                        {s.gender || '—'}
                      </span>
                    </td>
                    <td>{s.maritalstatus || '—'}</td>
                    <td>{s.doj || '—'}</td>
                    <td>
                      <div className={styles.actionGroup}>
                        {s.progress_regID >= 18 ? (
                          <button className={styles.viewBtn} title="View Staff Record">
                            <Users size={15} />
                          </button>
                        ) : (
                          <Link
                            href={`/dashboard/hr/employees/documentation/${s.id}`}
                            className={styles.docBtn}
                            title={`Continue Documentation (Step ${s.progress_regID || 0}/18)`}
                          >
                            <FileText size={15} />
                          </Link>
                        )}
                      </div>
                    </td>
                  </motion.tr>
                ))
              )}
            </tbody>
          </table>
        </div>

        {filtered.length > 0 && (
          <p className={styles.countNote}>
            Showing <strong>{filtered.length}</strong> of <strong>{staffList.length}</strong> records
          </p>
        )}
      </div>
    </motion.div>
  );
}
