import { Link, NavLink, Route, Routes } from 'react-router-dom'
import Home from './pages/Home'
import About from './pages/About'
import Services from './pages/Services'
import Contact from './pages/Contact'
import ClinicRegistration from './pages/ClinicRegistration'
import ClinicRegistrationWizard from './pages/ClinicRegistrationWizard'
import ChatbotWidget from './components/ChatbotWidget'
import LanguageSwitcher from './components/LanguageSwitcher'
import { useTranslation } from 'react-i18next'
import { useEffect, useRef, useState } from 'react'
import { useLocation, useNavigate } from 'react-router-dom'

function Layout() {
  const { t } = useTranslation()
  const location = useLocation()
  const navigate = useNavigate()
  const [showSubmissionSuccess, setShowSubmissionSuccess] = useState(false)
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false)
  const menuButtonRef = useRef(null)
  const menuCloseRef = useRef(null)

  useEffect(() => {
    setMobileMenuOpen(false)
  }, [location.pathname])

  useEffect(() => {
    if (!mobileMenuOpen) return undefined

    const previousOverflow = document.body.style.overflow
    const closeOnEscape = event => {
      if (event.key === 'Escape') setMobileMenuOpen(false)
    }

    document.body.style.overflow = 'hidden'
    menuCloseRef.current?.focus()
    window.addEventListener('keydown', closeOnEscape)

    return () => {
      document.body.style.overflow = previousOverflow
      window.removeEventListener('keydown', closeOnEscape)
      menuButtonRef.current?.focus()
    }
  }, [mobileMenuOpen])

  useEffect(() => {
    if (location.state?.clinicSubmissionSuccess) {
      setShowSubmissionSuccess(true)
      navigate(location.pathname, { replace: true, state: null })
    }
  }, [location.pathname, location.state, navigate])

  return (
    <>
      <header className="site-header">
        <Link className="brand" to="/">CarePoint<span>+</span></Link>
        <nav className="desktop-nav" aria-label={t('nav.aria')}>
          <NavLink to="/" end>{t('nav.home')}</NavLink>
          <NavLink to="/about">{t('nav.about')}</NavLink>
          <NavLink to="/services">{t('nav.services')}</NavLink>
          <NavLink to="/contact">{t('nav.contact')}</NavLink>
        </nav>
        <div className="site-header-actions">
          <LanguageSwitcher />
          <Link className="button button-small" to="/register-clinic">{t('nav.register')}</Link>
        </div>
        <button
          ref={menuButtonRef}
          type="button"
          className="mobile-menu-toggle"
          aria-label={mobileMenuOpen ? t('nav.closeMenu') : t('nav.openMenu')}
          aria-expanded={mobileMenuOpen}
          onClick={() => setMobileMenuOpen(current => !current)}
        >
          <span className={`mobile-menu-icon${mobileMenuOpen ? ' is-open' : ''}`} aria-hidden="true">
            <span />
            <span />
            <span />
          </span>
        </button>
        {mobileMenuOpen && (
          <>
            <button
              type="button"
              className="mobile-menu-backdrop"
              aria-label={t('nav.closeMenu')}
              onClick={() => setMobileMenuOpen(false)}
            />
            <aside id="mobile-side-menu" className="mobile-side-menu" aria-label={t('nav.aria')}>
              <div className="mobile-side-menu-header">
                <Link className="brand" to="/" onClick={() => setMobileMenuOpen(false)}>CarePoint<span>+</span></Link>
                <button
                  ref={menuCloseRef}
                  type="button"
                  className="mobile-menu-close"
                  aria-label={t('nav.closeMenu')}
                  onClick={() => setMobileMenuOpen(false)}
                >
                  <span aria-hidden="true">×</span>
                </button>
              </div>
              <nav className="mobile-side-menu-links" aria-label={t('nav.aria')}>
                <NavLink to="/" end onClick={() => setMobileMenuOpen(false)}>{t('nav.home')}</NavLink>
                <NavLink to="/about" onClick={() => setMobileMenuOpen(false)}>{t('nav.about')}</NavLink>
                <NavLink to="/services" onClick={() => setMobileMenuOpen(false)}>{t('nav.services')}</NavLink>
                <NavLink to="/contact" onClick={() => setMobileMenuOpen(false)}>{t('nav.contact')}</NavLink>
              </nav>
              <div className="mobile-side-menu-actions">
                <LanguageSwitcher />
                <Link className="button" to="/register-clinic" onClick={() => setMobileMenuOpen(false)}>
                  {t('nav.register')}
                </Link>
              </div>
            </aside>
          </>
        )}
      </header>
      <main>
        <Routes>
          <Route path="/" element={<Home />} />
          <Route path="/about" element={<About />} />
          <Route path="/services" element={<Services />} />
          <Route path="/contact" element={<Contact />} />
          <Route path="/register-clinic" element={<ClinicRegistration />} />
          <Route path="/register-clinic-wizard" element={<ClinicRegistrationWizard />} />
          <Route path="*" element={<section className="container section"><h1>{t('notFound.title')}</h1><Link to="/">{t('notFound.returnHome')}</Link></section>} />
        </Routes>
      </main>
      <footer className="footer">
        <div className="container footer-inner">
          <div><Link className="brand brand-light" to="/">CarePoint<span>+</span></Link><p>{t('home.footerTagline')}</p></div>
          <div className="footer-links"><Link to="/about">{t('nav.about')}</Link><Link to="/services">{t('nav.services')}</Link><Link to="/contact">{t('nav.contact')}</Link></div>
          <small>© {new Date().getFullYear()} CarePoint Medical. {t('home.demoFooter')}</small>
        </div>
      </footer>
      <ChatbotWidget />
      {showSubmissionSuccess && (
        <div className="success-modal-backdrop">
          <section className="success-modal" role="dialog" aria-modal="true" aria-labelledby="submission-success-title">
            <span className="success-modal-icon" aria-hidden="true">✓</span>
            <h2 id="submission-success-title">{t('form.submissionSuccessTitle')}</h2>
            <p>{t('form.submissionSuccessMessage')}</p>
            <button className="button" type="button" onClick={() => setShowSubmissionSuccess(false)}>
              {t('form.submissionSuccessDismiss')}
            </button>
          </section>
        </div>
      )}
    </>
  )
}

export default function App() {
  return <Layout />
}
