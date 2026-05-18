"use client";

import { useState, useEffect } from 'react';
import { motion } from 'framer-motion';
import axios from 'axios';
import { useRouter } from 'next/navigation';
import { UserPlus, Search, ChevronDown, X, Eye } from 'lucide-react';
import styles from './page.module.css';

const API_BASE = process.env.NEXT_PUBLIC_API_URL || 'http://127.0.0.1:8000/api/nextjs';

const TITLES = ['Mr', 'Mrs', 'Miss', 'Dr', 'Prof', 'Alhaji', 'Alhaja', 'Chief', 'Engr', 'Barr'];
const GENDERS = ['Male', 'Female'];
const MARITAL = ['Single', 'Married', 'Divorced', 'Widowed'];

const initialForm = {
  title: '', surname: '', firstname: '', othernames: '',
  sex: '', maritalStatus: '', email: '', phoneNo: '',
  date_of_birth: '', date_of_joining: '',
  department_id: '', unit_id: '', designation_id: '',
  iou: '', address: '',
};

// ── Helper: field row ────────────────────────────────────────────────────
const Field = ({ label, name, type = 'text', placeholder = '', value, onChange, error }) => (
  <div className={styles.fieldGroup}>
    <label className={styles.label}>{label}</label>
    <input
      type={type}
      name={name}
      value={value}
      onChange={onChange}
      placeholder={placeholder}
      className={`${styles.input} ${error ? styles.inputError : ''}`}
    />
    {error && <span className={styles.errorMsg}>{error[0]}</span>}
  </div>
);

const Select = ({ label, name, options, placeholder = 'Select…', value, onChange, error }) => (
  <div className={styles.fieldGroup}>
    <label className={styles.label}>{label}</label>
    <div className={styles.selectWrap}>
      <select
        name={name}
        value={value}
        onChange={onChange}
        className={`${styles.input} ${error ? styles.inputError : ''}`}
      >
        <option value="">{placeholder}</option>
        {(Array.isArray(options) ? options : []).map(o => (
          <option key={o.id ?? o} value={o.id ?? o}>{o.name ?? o}</option>
        ))}
      </select>
      <ChevronDown size={16} className={styles.selectIcon} />
    </div>
    {error && <span className={styles.errorMsg}>{error[0]}</span>}
  </div>
);

export default function AddNewStaff() {
  const router = useRouter();
  const [form, setForm]               = useState(initialForm);
  const [departments, setDepartments] = useState([]);
  const [units, setUnits]             = useState([]);
  const [designations, setDesignations] = useState([]);
  const [submitting, setSubmitting]   = useState(false);
  const [toast, setToast]             = useState(null);
  const [errors, setErrors]           = useState({});

  // ── Load form data on mount ──────────────────────────────────────────────
  useEffect(() => {
    axios.get(`${API_BASE}/hr/add-staff/form-data`)
      .then(res => {
        if (res.data.status === 'success') {
          setDepartments(res.data.departments || []);
        }
      })
      .catch((err) => {
        console.error('Failed to load form data:', err?.response?.data || err.message);
      });
  }, []);

  // ── Cascade: department → designations + units ───────────────────────────
  useEffect(() => {
    if (!form.department_id) { setDesignations([]); setUnits([]); return; }
    axios.get(`${API_BASE}/hr/add-staff/designations/${form.department_id}`)
      .then(r => setDesignations(r.data));
    axios.get(`${API_BASE}/hr/add-staff/units/${form.department_id}`)
      .then(r => setUnits(r.data));
  }, [form.department_id]);

  const handleChange = (e) => {
    const { name, value } = e.target;
    setForm(prev => ({ ...prev, [name]: value }));
    setErrors(prev => ({ ...prev, [name]: undefined }));
  };

  const handleTextareaResize = (e) => {
    e.target.style.height = 'auto';
    e.target.style.height = e.target.scrollHeight + 'px';
    handleChange(e);
  };

  const showToast = (msg, type = 'success') => {
    setToast({ msg, type });
    setTimeout(() => setToast(null), 4000);
  };

  const handleSubmit = async (e) => {
    e.preventDefault();
    setSubmitting(true);
    setErrors({});
    try {
      await axios.post(`${API_BASE}/hr/add-staff`, form);
      showToast('Staff record added successfully!');
      setForm(initialForm);
      setDesignations([]);
      setUnits([]);
    } catch (err) {
      if (err.response?.status === 422) {
        setErrors(err.response.data.errors || {});
        showToast('Please fix the errors below.', 'error');
      } else {
        showToast(err.response?.data?.message || 'An error occurred.', 'error');
      }
    } finally {
      setSubmitting(false);
    }
  };



  // Removed inline components to prevent focus loss
  return (
    <motion.div initial={{ opacity: 0 }} animate={{ opacity: 1 }}>
      {/* ── Toast ── */}
      {toast && (
        <div className={`${styles.toast} ${toast.type === 'error' ? styles.toastError : styles.toastSuccess}`}>
          {toast.msg}
        </div>
      )}

      {/* ── Page Header ── */}
      <div className={styles.pageHeader}>
        <div>
          <h1 className={styles.pageTitle}>Add New Staff</h1>
          <p className={styles.pageSubtitle}>Register a new administrative staff member into the system.</p>
        </div>
      </div>

      {/* ── Form Card ── */}
      <div className={`premium-card ${styles.formCard}`}>
        <div className={styles.formCardHeader}>
          <div style={{ display: 'flex', alignItems: 'center', gap: '0.75rem' }}>
            <UserPlus size={22} />
            <h2>Staff Registration Form</h2>
          </div>
          <button 
            onClick={() => router.push('/dashboard/hr/employees')} 
            className={styles.closeBtn}
            title="Close Form"
          >
            <X size={20} />
          </button>
        </div>

        <form onSubmit={handleSubmit} noValidate>
          <div className={styles.grid}>
            {/* ─ Personal Info ─ */}
            <Select label="Title *" name="title" options={TITLES.map(t => ({ id: t, name: t }))} value={form.title} onChange={handleChange} error={errors.title} />
            <Field label="Surname *" name="surname" placeholder="e.g. IBRAHIM" value={form.surname} onChange={handleChange} error={errors.surname} />
            <Field label="First Name *" name="firstname" placeholder="e.g. AMINU" value={form.firstname} onChange={handleChange} error={errors.firstname} />
            <Field label="Other Names" name="othernames" placeholder="e.g. SULEIMAN" value={form.othernames} onChange={handleChange} error={errors.othernames} />
            <Select label="Gender *" name="sex" options={GENDERS.map(g => ({ id: g, name: g }))} value={form.sex} onChange={handleChange} error={errors.sex} />
            <Select label="Marital Status *" name="maritalStatus" options={MARITAL.map(m => ({ id: m, name: m }))} value={form.maritalStatus} onChange={handleChange} error={errors.maritalStatus} />
            <Field label="Date of Birth *" name="date_of_birth" type="date" value={form.date_of_birth} onChange={handleChange} error={errors.date_of_birth} />
            <Field label="Phone Number" name="phoneNo" placeholder="e.g. 08012345678" value={form.phoneNo} onChange={handleChange} error={errors.phoneNo} />
            <Field label="Email Address" name="email" type="email" placeholder="e.g. staff@isalu.gov.ng" value={form.email} onChange={handleChange} error={errors.email} />

            {/* ─ Address ─ */}
            <div className={styles.fieldGroup}>
              <label className={styles.label}>Residential Address *</label>
              <textarea
                name="address"
                value={form.address}
                onChange={handleTextareaResize}
                rows={1}
                placeholder="Enter full residential address"
                className={`${styles.input} ${styles.textarea} ${errors.address ? styles.inputError : ''}`}
                style={{ overflow: 'hidden', minHeight: '42px' }}
              />
              {errors.address && <span className={styles.errorMsg}>{errors.address[0]}</span>}
            </div>

            {/* ─ Appointment Details ─ */}
            <Select
              label="Department *"
              name="department_id"
              options={departments}
              placeholder="— Select Department —"
              value={form.department_id} onChange={handleChange} error={errors.department_id}
            />
            <Select
              label="Unit *"
              name="unit_id"
              options={units}
              placeholder={form.department_id ? '— Select Unit —' : '— Choose Department First —'}
              value={form.unit_id} onChange={handleChange} error={errors.unit_id}
            />
            <Select
              label="Designation *"
              name="designation_id"
              options={designations}
              placeholder={form.department_id ? '— Select Designation —' : '— Choose Department First —'}
              value={form.designation_id} onChange={handleChange} error={errors.designation_id}
            />
            <Field label="Date of Joining *" name="date_of_joining" type="date" value={form.date_of_joining} onChange={handleChange} error={errors.date_of_joining} />
            <Field label="IOU Cap" name="iou" type="number" placeholder="e.g. 500000" value={form.iou} onChange={handleChange} error={errors.iou} />
          </div>

          {/* ─ Submit ─ */}
          <div className={styles.formFooter}>
            <div style={{ display: 'flex', gap: '1rem' }}>
              <button
                type="button"
                className={styles.btnSecondary}
                onClick={() => router.push('/dashboard/hr/employees')}
              >
                Cancel
              </button>
              <button
                type="button"
                className={styles.btnSecondary}
                onClick={() => { setForm(initialForm); setErrors({}); }}
              >
                Clear Form
              </button>
            </div>
            <button type="submit" className={`premium-btn ${styles.btnPrimary}`} disabled={submitting}>
              {submitting ? 'Saving…' : 'Save Staff Record'}
            </button>
          </div>
        </form>
      </div>


    </motion.div>
  );
}
