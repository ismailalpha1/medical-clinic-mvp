import { useRef, useState } from 'react'
import { Link, useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import { postJson } from '../lib/api'
import LocationFields from '../components/LocationFields'

const initial = {
  clinic_name: '', clinic_type: '', website: '', year_established: '',
  country: '', city: '', postal_code: '', address: '', number_of_doctors: '',
  number_of_locations: '1', services: '', description: '', specialties: [],
  contact_person: '', email: '', phone: '', preferred_contact_method: 'Email', preferred_language: 'en',
  goals: [], consent: false,
}

export default function ClinicRegistrationWizard() {
  const { t, i18n } = useTranslation()
  const navigate = useNavigate()
  const [form, setForm] = useState(() => ({
    ...initial,
    preferred_language: i18n.language.startsWith('es') ? 'es' : 'en',
  }))
  const [slide, setSlide] = useState(0)
  const [status, setStatus] = useState({ loading: false, message: '', errors: {}, error: false })
  const touchStart = useRef(null)
  const formRef = useRef(null)

  const specialtyOptions = [
    ['family_medicine', t('form.specialtyFamily')],
    ['dentistry', t('form.specialtyDentistry')],
    ['cardiology', t('form.specialtyCardiology')],
    ['dermatology', t('form.specialtyDermatology')],
    ['pediatrics', t('form.specialtyPediatrics')],
    ['orthopedics', t('form.specialtyOrthopedics')],
    ['gynecology', t('form.specialtyGynecology')],
    ['diagnostics', t('form.specialtyDiagnostics')],
    ['other', t('common.other')],
  ]
  const goalOptions = [
    ['automate_appointments', t('form.goalAppointments')],
    ['patient_coordination', t('form.goalCoordination')],
    ['postoperative_follow_up', t('form.goalPostOp')],
    ['reduce_admin_work', t('form.goalAdmin')],
    ['connect_clinic_tools', t('form.goalIntegrations')],
    ['other', t('common.other')],
  ]

  const slides = [
    {
      title: t('wizard.basicTitle'),
      fields: ['clinic_name', 'clinic_type', 'website', 'year_established'],
    },
    {
      title: t('wizard.locationTitle'),
      fields: ['location_fields', 'address', 'number_of_doctors'],
    },
    {
      title: t('wizard.servicesTitle'),
      fields: ['number_of_locations', 'services', 'description', 'specialties'],
    },
    {
      title: t('wizard.contactTitle'),
      fields: ['contact_person', 'email', 'phone', 'preferred_contact_method', 'preferred_language'],
    },
    {
      title: t('wizard.goalsTitle'),
      fields: ['goals', 'consent', 'review_clinic', 'review_contact'],
    },
  ]

  function update(event) {
    const { name, value, type, checked } = event.target
    setForm(current => ({ ...current, [name]: type === 'checkbox' ? checked : value }))
  }

  function updateCountry(event) {
    const { value } = event.target
    setForm(current => ({ ...current, country: value, city: '' }))
  }

  function updateMulti(event) {
    const { name, value, checked } = event.target
    setForm(current => ({
      ...current,
      [name]: checked ? [...current[name], value] : current[name].filter(item => item !== value),
    }))
  }

  function next() {
    if (slide < slides.length - 1 && formRef.current?.reportValidity()) {
      setSlide(current => current + 1)
    }
  }

  function back() {
    if (slide > 0) setSlide(current => current - 1)
  }

  function onTouchStart(event) {
    touchStart.current = event.changedTouches[0].clientX
  }

  function onTouchEnd(event) {
    if (touchStart.current === null) return
    const distance = event.changedTouches[0].clientX - touchStart.current
    if (distance > 55) back()
    if (distance < -55) next()
    touchStart.current = null
  }

  async function submit(event) {
    event.preventDefault()
    setStatus({ loading: true, message: '', errors: {}, error: false })
    try {
      const locale = i18n.language.startsWith('es') ? 'es' : 'en'
      await postJson('/clinic-submissions', { ...form, locale })
      setForm(initial)
      setSlide(0)
      navigate('/', { state: { clinicSubmissionSuccess: true } })
    } catch (error) {
      const errors = error.errors || {}
      setStatus({
        loading: false,
        message: Object.keys(errors).length
          ? t('form.validationError')
          : error.code === 'NETWORK_ERROR'
            ? t('form.networkError')
            : error.status === 404
              ? t('form.apiUnavailableError')
            : error.status === 503
              ? t('form.emailDeliveryError')
              : t('form.submitError'),
        errors,
        error: true,
      })
    }
  }

  function fieldError(name) {
    return status.errors[name] ? t('form.validationFieldError') : ''
  }

  function renderQuestion(name) {
    if (name === 'clinic_name') return <WizardField name={name} label={t('form.clinicNameField')} form={form} onChange={update} error={fieldError(name)} required />
    if (name === 'clinic_type') return <WizardField name={name} label={t('form.clinicTypeField')} form={form} onChange={update} error={fieldError(name)} placeholder={t('form.clinicTypePlaceholder')} required />
    if (name === 'website') return <WizardField name={name} label={t('form.websiteField')} form={form} onChange={update} error={fieldError(name)} type="url" placeholder="https://example.com" />
    if (name === 'year_established') return <WizardField name={name} label={t('form.yearField')} form={form} onChange={update} error={fieldError(name)} type="number" placeholder={t('form.yearPlaceholder')} />
    if (name === 'location_fields') return (
      <LocationFields
        key={name}
        form={form}
        onChange={update}
        onCountryChange={updateCountry}
        fieldError={fieldError}
        className="wizard-grid location-fields"
      />
    )
    if (name === 'address') return <WizardField name={name} label={t('form.addressField')} form={form} onChange={update} error={fieldError(name)} />
    if (name === 'number_of_doctors') return <WizardField name={name} label={t('form.doctorsField')} form={form} onChange={update} error={fieldError(name)} placeholder={t('form.doctorsPlaceholder')} />
    if (name === 'number_of_locations') return <WizardField name={name} label={t('form.locationsField')} form={form} onChange={update} error={fieldError(name)} placeholder={t('form.locationsPlaceholder')} />
    if (name === 'services') return (
      <label className="field wizard-question"><span>{t('form.servicesField')}</span><textarea name={name} rows="4" value={form[name]} onChange={update} placeholder={t('form.servicesPlaceholder')} /></label>
    )
    if (name === 'description') return (
      <label className="field wizard-question"><span>{t('form.aboutField')}</span><textarea name={name} rows="4" value={form[name]} onChange={update} placeholder={t('form.aboutPlaceholder')} /></label>
    )
    if (name === 'specialties') return (
      <fieldset className="wizard-question wizard-options">
        <legend>{t('form.specialties')}</legend>
        <div className="check-grid">{specialtyOptions.map(([value, label]) =>
          <label className="check-option" key={value}><input type="checkbox" name="specialties" value={value} checked={form.specialties.includes(value)} onChange={updateMulti} />{label}</label>
        )}</div>
      </fieldset>
    )
    if (name === 'contact_person') return <WizardField name={name} label={t('form.contactNameField')} form={form} onChange={update} error={fieldError(name)} required />
    if (name === 'email') return <WizardField name={name} label={t('form.businessEmailField')} form={form} onChange={update} error={fieldError(name)} type="email" required />
    if (name === 'phone') return <WizardField name={name} label={t('form.phoneField')} form={form} onChange={update} error={fieldError(name)} type="tel" required />
    if (name === 'preferred_contact_method') return (
      <label className="field wizard-question"><span>{t('form.preferredMethod')}</span><select name={name} value={form[name]} onChange={update}><option value="Email">{t('common.email')}</option><option value="Phone">{t('common.phone')}</option><option value="WhatsApp">WhatsApp</option></select></label>
    )
    if (name === 'preferred_language') return (
      <label className="field wizard-question"><span>{t('form.preferredLanguage')}</span><select name={name} value={form[name]} onChange={update} required><option value="en">{t('form.languageEnglish')}</option><option value="es">{t('form.languageSpanish')}</option></select></label>
    )
    if (name === 'goals') return (
      <fieldset className="wizard-question wizard-options">
        <legend>{t('form.goals')}</legend>
        <div className="check-grid">{goalOptions.map(([value, label]) =>
          <label className="check-option" key={value}><input type="checkbox" name="goals" value={value} checked={form.goals.includes(value)} onChange={updateMulti} />{label}</label>
        )}</div>
      </fieldset>
    )
    if (name === 'consent') return (
      <label className="check-option consent wizard-consent"><input type="checkbox" name="consent" checked={form.consent} onChange={update} required />{t('form.consent')}</label>
    )
    if (name === 'review_clinic') return (
      <div className="wizard-review">
        <strong>{t('wizard.reviewTitle')}</strong>
        <span>{form.clinic_name || t('wizard.notProvided')}</span>
        <span>{form.contact_person || t('wizard.notProvided')}</span>
      </div>
    )
    if (name === 'review_contact') return (
      <div className="wizard-review">
        <strong>{t('wizard.contactReviewTitle')}</strong>
        <span>{form.email || t('wizard.notProvided')}</span>
        <span>{form.phone || t('wizard.notProvided')}</span>
        <span>{form.preferred_contact_method}</span>
        <span>{form.preferred_language === 'es' ? t('form.languageSpanish') : t('form.languageEnglish')}</span>
      </div>
    )
    return null
  }

  return (
    <section className="section container wizard-page">
      <div className="wizard-heading">
        <div>
          <span className="eyebrow">{t('wizard.eyebrow')}</span>
          <h1>{t('wizard.title')}</h1>
        </div>
        <Link className="text-link" to="/register-clinic">{t('wizard.standardLink')} →</Link>
      </div>

      <form ref={formRef} className="wizard-card" onSubmit={submit} onTouchStart={onTouchStart} onTouchEnd={onTouchEnd} onKeyDown={event => {
        if (['TEXTAREA', 'INPUT', 'SELECT'].includes(event.target.tagName)) return
        if (event.key === 'ArrowRight') next()
        if (event.key === 'ArrowLeft') back()
      }}>
        <div className="wizard-progress" aria-label={t('wizard.progress')}>
          {slides.map((item, index) => (
            <button type="button" key={item.title} className={index === slide ? 'active' : index < slide ? 'complete' : ''} onClick={() => setSlide(index)} aria-label={`${index + 1}: ${item.title}`} />
          ))}
        </div>

        {status.message && <div className={status.error ? 'notice error' : 'notice success'} role={status.error ? 'alert' : 'status'}>{status.message}</div>}

        <div className="wizard-slide">
          <span className="wizard-count">{slide + 1} / {slides.length}</span>
          <h2>{slides[slide].title}</h2>
          <div className="wizard-grid">
            {slides[slide].fields.map(name => name === 'location_fields'
              ? renderQuestion(name)
              : <div key={name}>{renderQuestion(name)}</div>)}
          </div>
        </div>

        <div className="wizard-actions">
          <button type="button" className="wizard-back" onClick={back} disabled={slide === 0}>{t('wizard.back')}</button>
          {slide < slides.length - 1
            ? <button type="button" className="button" onClick={next}>{t('wizard.next')}</button>
            : <button type="submit" className="button" disabled={status.loading}>{status.loading ? t('form.submitting') : t('wizard.submit')}</button>}
        </div>
        <p className="wizard-hint">{t('wizard.swipeHint')}</p>
      </form>
    </section>
  )
}

function WizardField({ name, label, form, onChange, error, type = 'text', placeholder, required = false }) {
  return (
    <label className="field wizard-question">
      <span>{label}</span>
      <input
        name={name}
        type={type}
        value={form[name]}
        onChange={onChange}
        placeholder={placeholder}
        required={required}
      />
      {error && <small className="field-error">{error}</small>}
    </label>
  )
}
