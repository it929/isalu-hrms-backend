"use client";

import { useState, useEffect, useCallback } from 'react';
import { motion, AnimatePresence } from 'framer-motion';
import axios from 'axios';
import {
  Briefcase,
  Plus,
  Edit2,
  Trash2,
  Loader2,
  CheckCircle2,
  XCircle,
  AlertCircle
} from 'lucide-react';
import styles from './page.module.css';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/nextjs';

function getUserId() {
  if (typeof window === 'undefined') return null;
  try {
    const raw = localStorage.getItem('hrms_user');
    if (raw) {
      const user = JSON.parse(raw);
      return user?.id ?? null;
    }
  } catch { /* ignore */ }
  return null;
}

function buildHeaders() {
  const uid = getUserId();
  return uid ? { 'X-User-Id': uid } : {};
}

export default function DesignationPage() {
  const [loading, setLoading] = useState(true);
  const [saving, setSaving] = useState(false);
  const [toast, setToast] = useState(null);

  const [courtList, setCourtList] = useState([]);
  const [departmentList, setDepartmentList] = useState([]);
  const [designationList, setDesignationList] = useState([]);
  const [courtInfo, setCourtInfo] = useState({ courtstatus: 1 });

  // Form & Edit states
  const [editId, setEditId] = useState(null);
  const [formData, setFormData] = useState({
    court: '',
    department: '',
    designation: ''
  });

  // Confirm delete modal state
  const [confirmModal, setConfirmModal] = useState({ open: false, item: null });

  const showToast = useCallback((message, type = 'success') => {
    setToast({ message, type });
    setTimeout(() => setToast(null), 4000);
  }, []);

  const fetchData = useCallback(async (silent = false) => {
    if (!silent) setLoading(true);
    try {
      const res = await axios.get(`${API_BASE}/hr/basic/designation`, { headers: buildHeaders() });
      if (res.data.status === 'success') {
        setCourtList(res.data.CourtList || []);
        setDepartmentList(res.data.DepartmentList || []);
        setDesignationList(res.data.DesignationList || []);
        setCourtInfo(res.data.CourtInfo || { courtstatus: 1 });
      } else {
        showToast(res.data.message || 'Failed to load data.', 'error');
      }
    } catch (err) {
      showToast('Failed to connect to server.', 'error');
    } finally {
      if (!silent) setLoading(false);
    }
  }, [showToast]);

  useEffect(() => {
    fetchData();
  }, [fetchData]);

  const handleInputChange = (e) => {
    const { name, value } = e.target;
    setFormData(prev => {
      const updated = { ...prev, [name]: value };
      // If we change court, reset department selection to prevent invalid pairings
      if (name === 'court') {
        updated.department = '';
      }
      return updated;
    });
  };

  const handleEdit = (item) => {
    setEditId(item.id);
    setFormData({
      court: item.courtID || '',
      department: item.departmentID || '',
      designation: item.designation
    });
  };

  const handleCancelEdit = () => {
    setEditId(null);
    setFormData({
      court: '',
      department: '',
      designation: ''
    });
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    const { designation, department, court } = formData;
    if (!designation) return showToast('Please enter a designation.', 'warning');
    if (!department) return showToast('Please select a department.', 'warning');
    if (courtInfo.courtstatus === 1 && !court) return showToast('Please select a court.', 'warning');

    setSaving(true);
    try {
      let res;
      const selectedDept = departmentList.find(d => String(d.id) === String(department));
      const resolvedCourt = selectedDept ? selectedDept.courtID : (courtInfo.courtstatus === 0 ? courtInfo.courtid : court);

      if (editId) {
        // Edit Designation
        const payload = {
          PostID: editId,
          designation,
          DeptID: department,
          CourtID: resolvedCourt
        };
        res = await axios.post(`${API_BASE}/hr/basic/designation/edit`, payload, { headers: buildHeaders() });
      } else {
        // Add Designation
        const payload = {
          add: true,
          designation,
          department,
          court: resolvedCourt
        };
        res = await axios.post(`${API_BASE}/hr/basic/designation`, payload, { headers: buildHeaders() });
      }

      if (res.data.status === 'success') {
        showToast(res.data.message || `Designation ${editId ? 'updated' : 'added'} successfully.`, 'success');
        handleCancelEdit();
        fetchData(true);
      } else {
        showToast(res.data.message || `Failed to ${editId ? 'update' : 'add'} designation.`, 'error');
      }
    } catch (err) {
      showToast(err.response?.data?.message || 'Server error while saving.', 'error');
    } finally {
      setSaving(false);
    }
  };

  const handleDeleteClick = (item) => {
    setConfirmModal({ open: true, item });
  };

  const handleConfirmDelete = async () => {
    const item = confirmModal.item;
    setConfirmModal({ open: false, item: null });

    try {
      const payload = {
        PostID: item.id,
        depty: item.departmentID,
        courty: item.courtID
      };
      const res = await axios.post(`${API_BASE}/hr/basic/designation/delete`, payload, { headers: buildHeaders() });
      if (res.data.status === 'success') {
        showToast('Designation deleted successfully.', 'success');
        if (editId === item.id) {
          handleCancelEdit();
        }
        fetchData(true);
      } else {
        showToast(res.data.message || 'Failed to delete designation.', 'error');
      }
    } catch (err) {
      showToast(err.response?.data?.message || 'Server error while deleting.', 'error');
    }
  };

  if (loading) {
    return (
      <div className={styles.loadingState}>
        <Loader2 className={styles.spinner} size={40} />
        <p>Loading Designation Data...</p>
      </div>
    );
  }

  // Filter departments based on selected court (if multi-court mode)
  const filteredDepartments = departmentList.filter(
    (d) => !formData.court || String(d.courtID) === String(formData.court)
  );

  return (
    <motion.div 
      className={styles.container}
      initial={{ opacity: 0, y: 15 }}
      animate={{ opacity: 1, y: 0 }}
      transition={{ duration: 0.4 }}
    >
      <div className={styles.header}>
        <h1><Briefcase size={26} style={{ strokeWidth: 2.2 }} /> Designation Setup</h1>
        <p>Manage job titles and designations.</p>
      </div>

      <div className={styles.layoutGrid}>
        {/* Form Card */}
        <div className={styles.card}>
          <h2 className={styles.cardTitle}>
            {editId ? 'Edit Designation' : 'Add Designation'}
          </h2>
          
          <form className={styles.form} onSubmit={handleSubmit}>
            {courtInfo.courtstatus === 1 && (
              <div className={styles.formGroup}>
                <label htmlFor="court" className={styles.label}>Court</label>
                <select 
                  id="court"
                  name="court"
                  className={styles.input} 
                  value={formData.court} 
                  onChange={handleInputChange}
                  required
                >
                  <option value="">-- Select Court --</option>
                  {courtList.map((c) => (
                    <option key={c.id} value={c.id}>{c.court_name}</option>
                  ))}
                </select>
              </div>
            )}
            
            <div className={styles.formGroup}>
              <label htmlFor="department" className={styles.label}>Department</label>
              <select 
                id="department"
                name="department"
                className={styles.input} 
                value={formData.department} 
                onChange={handleInputChange}
                required
              >
                <option value="">-- Select Department --</option>
                {filteredDepartments.map((d) => (
                  <option key={d.id} value={d.id}>{d.department}</option>
                ))}
              </select>
            </div>

            <div className={styles.formGroup}>
              <label htmlFor="designation" className={styles.label}>Designation Name</label>
              <input 
                type="text" 
                id="designation"
                name="designation"
                className={styles.input} 
                placeholder="Input designation name" 
                value={formData.designation}
                onChange={handleInputChange}
                required
              />
            </div>
            
            <div className={styles.buttonGroup}>
              {editId && (
                <button 
                  type="button" 
                  className={styles.cancelBtn}
                  onClick={handleCancelEdit}
                >
                  Cancel
                </button>
              )}
              <button 
                type="submit" 
                className={styles.submitBtn} 
                disabled={saving}
              >
                {saving ? (
                  <>
                    <Loader2 size={16} className={styles.spinner} />
                    <span>Saving...</span>
                  </>
                ) : (
                  <>
                    {editId ? <Edit2 size={16} /> : <Plus size={16} />}
                    <span>{editId ? 'Update' : 'Create'}</span>
                  </>
                )}
              </button>
            </div>
          </form>
        </div>

        {/* List Card */}
        <div className={styles.card}>
          <h2 className={styles.cardTitle}>Designation List</h2>
          <div className={styles.tableWrapper}>
            <table className={styles.table}>
              <thead>
                <tr>
                  <th style={{ width: '60px' }}>S/N</th>
                  {courtInfo.courtstatus === 1 && <th>Court</th>}
                  <th>Department</th>
                  <th>Designation</th>
                  <th style={{ width: '120px', textAlign: 'center' }}>Actions</th>
                </tr>
              </thead>
              <tbody>
                {designationList.length === 0 ? (
                  <tr>
                    <td colSpan={courtInfo.courtstatus === 1 ? 5 : 4} style={{ textAlign: 'center', padding: '3rem 0' }}>
                      No designations found.
                    </td>
                  </tr>
                ) : (
                  designationList.map((item, index) => (
                    <tr key={item.id}>
                      <td>{index + 1}</td>
                      {courtInfo.courtstatus === 1 && <td>{item.court_name || 'N/A'}</td>}
                      <td>{item.department || 'N/A'}</td>
                      <td style={{ fontWeight: '500' }}>{item.designation}</td>
                      <td>
                        <div className={styles.actions}>
                          <button
                            className={`${styles.actionBtn} ${styles.editBtn}`}
                            title="Edit"
                            onClick={() => handleEdit(item)}
                          >
                            <Edit2 size={16} />
                          </button>
                          <button
                            className={`${styles.actionBtn} ${styles.deleteBtn}`}
                            title="Delete"
                            onClick={() => handleDeleteClick(item)}
                          >
                            <Trash2 size={16} />
                          </button>
                        </div>
                      </td>
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        </div>
      </div>

      {/* Delete Confirmation Modal */}
      <AnimatePresence>
        {confirmModal.open && (
          <motion.div
            className={styles.modalOverlay}
            initial={{ opacity: 0 }}
            animate={{ opacity: 1 }}
            exit={{ opacity: 0 }}
            onClick={() => setConfirmModal({ open: false, item: null })}
          >
            <motion.div
              className={styles.modalBox}
              initial={{ opacity: 0, scale: 0.9, y: -20 }}
              animate={{ opacity: 1, scale: 1, y: 0 }}
              exit={{ opacity: 0, scale: 0.9, y: -20 }}
              transition={{ type: 'spring', stiffness: 300, damping: 25 }}
              onClick={(e) => e.stopPropagation()}
            >
              <div className={styles.modalIcon}>
                <Trash2 size={28} />
              </div>
              <h3 className={styles.modalTitle}>Delete Designation</h3>
              <p className={styles.modalMessage}>
                Are you sure you want to delete designation <strong>&ldquo;{confirmModal.item?.designation}&rdquo;</strong>?
                This action cannot be undone.
              </p>
              <div className={styles.modalActions}>
                <button
                  className={styles.modalCancelBtn}
                  onClick={() => setConfirmModal({ open: false, item: null })}
                >
                  Cancel
                </button>
                <button
                  className={styles.modalDeleteBtn}
                  onClick={handleConfirmDelete}
                >
                  <Trash2 size={15} />
                  Delete
                </button>
              </div>
            </motion.div>
          </motion.div>
        )}
      </AnimatePresence>

      {/* Toast Notification */}
      <AnimatePresence>
        {toast && (
          <motion.div 
            className={`${styles.toast} ${
              toast.type === 'error' ? styles.toastError : 
              toast.type === 'warning' ? styles.toastWarning : 
              styles.toastSuccess
            }`}
            initial={{ opacity: 0, y: 50, scale: 0.9 }}
            animate={{ opacity: 1, y: 0, scale: 1 }}
            exit={{ opacity: 0, scale: 0.9, transition: { duration: 0.2 } }}
          >
            {toast.type === 'error' ? <XCircle size={18} /> : 
             toast.type === 'warning' ? <AlertCircle size={18} /> : 
             <CheckCircle2 size={18} />}
            <span style={{ fontSize: '0.85rem', fontWeight: '500' }}>{toast.message}</span>
          </motion.div>
        )}
      </AnimatePresence>
    </motion.div>
  );
}
