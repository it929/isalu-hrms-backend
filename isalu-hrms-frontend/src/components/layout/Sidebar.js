"use client";

import Link from 'next/link';
import { usePathname } from 'next/navigation';
import { useSession } from '../../contexts/SessionContext';
import { useSidebar } from '../../contexts/SidebarContext';
import { useState } from 'react';
import { 
  LayoutDashboard, 
  Users, 
  Settings, 
  ShieldCheck, 
  Briefcase,
  LogOut,
  ChevronDown,
  UserCircle,
  CalendarDays,
  TrendingUp,
  FileText,
  BookOpen,
  GraduationCap,
  Building2,
  ClipboardList,
  HeartPulse,
  Landmark,
} from 'lucide-react';
import styles from './Sidebar.module.css';

const hrSubModules = [
  { name: 'Employee Records', path: '/dashboard/hr/employees', icon: <UserCircle size={16} /> },
  { name: 'Leave Management', path: '/dashboard/hr/leave', icon: <CalendarDays size={16} /> },
  { name: 'Performance & Promotions', path: '/dashboard/hr/performance', icon: <TrendingUp size={16} /> },
  { name: 'Pension & Gratuity', path: '/dashboard/hr/pension', icon: <Landmark size={16} /> },
  { name: 'Training', path: '/dashboard/hr/training', icon: <GraduationCap size={16} /> },
  { name: 'Open Registry', path: '/dashboard/hr/registry', icon: <BookOpen size={16} /> },
  { name: 'Manpower', path: '/dashboard/hr/manpower', icon: <Building2 size={16} /> },
  { name: 'Variation Records', path: '/dashboard/hr/variations', icon: <ClipboardList size={16} /> },
  { name: 'NHIS / Health', path: '/dashboard/hr/nhis', icon: <HeartPulse size={16} /> },
  { name: 'Reports', path: '/dashboard/hr/reports', icon: <FileText size={16} /> },
];

export default function Sidebar() {
  const pathname = usePathname();
  const { logout } = useSession();
  const { isCollapsed } = useSidebar();
  const [hrOpen, setHrOpen] = useState(pathname.startsWith('/dashboard/hr'));

  const isHrActive = pathname.startsWith('/dashboard/hr');

  const handleLogout = () => {
    logout();
    window.location.href = '/';
  };

  const topMenuItems = [
    { name: 'Dashboard', path: '/dashboard', icon: <LayoutDashboard size={20} /> },
  ];

  const bottomMenuItems = [
    { name: 'Role Management', path: '/dashboard/roles', icon: <ShieldCheck size={20} /> },
    { name: 'Technical Users', path: '/dashboard/technical-users', icon: <Settings size={20} /> },
    { name: 'HOD Assignments', path: '/dashboard/hod', icon: <Briefcase size={20} /> },
  ];

  return (
    <aside className={`${styles.sidebar} ${isCollapsed ? styles.collapsed : ''}`}>
      <nav className={styles.nav}>
        <ul className={styles.menuList}>
          {/* Top items */}
          {topMenuItems.map((item) => {
            const isActive = pathname === item.path;
            return (
              <li key={item.path}>
                <Link href={item.path} className={`${styles.menuItem} ${isActive ? styles.active : ''}`}>
                  <span className={styles.icon}>{item.icon}</span>
                  <span className={styles.text}>{item.name}</span>
                </Link>
              </li>
            );
          })}

          {/* HR Module with dropdown */}
          <li>
            <button
              className={`${styles.menuItem} ${styles.menuItemBtn} ${isHrActive ? styles.active : ''}`}
              onClick={() => setHrOpen((prev) => !prev)}
              aria-expanded={hrOpen}
            >
              <span className={styles.icon}><Users size={20} /></span>
              <span className={styles.text}>HR Module</span>
              <span className={`${styles.chevron} ${hrOpen ? styles.chevronOpen : ''}`}>
                <ChevronDown size={16} />
              </span>
            </button>

            {/* Sub-modules dropdown */}
            <div className={`${styles.subMenu} ${hrOpen ? styles.subMenuOpen : ''}`}>
              <ul className={styles.subMenuList}>
                {hrSubModules.map((sub) => {
                  const isSubActive = pathname === sub.path;
                  return (
                    <li key={sub.path}>
                      <Link
                        href={sub.path}
                        className={`${styles.subMenuItem} ${isSubActive ? styles.subMenuItemActive : ''}`}
                      >
                        <span className={styles.subIcon}>{sub.icon}</span>
                        <span>{sub.name}</span>
                      </Link>
                    </li>
                  );
                })}
              </ul>
            </div>
          </li>

          {/* Bottom items */}
          {bottomMenuItems.map((item) => {
            const isActive = pathname === item.path || pathname.startsWith(`${item.path}/`);
            return (
              <li key={item.path}>
                <Link href={item.path} className={`${styles.menuItem} ${isActive ? styles.active : ''}`}>
                  <span className={styles.icon}>{item.icon}</span>
                  <span className={styles.text}>{item.name}</span>
                </Link>
              </li>
            );
          })}
        </ul>
      </nav>
      
      <div className={styles.footer}>
        <button onClick={handleLogout} className={styles.logoutBtn}>
          <LogOut size={20} />
          <span>Logout</span>
        </button>
      </div>
    </aside>
  );
}

