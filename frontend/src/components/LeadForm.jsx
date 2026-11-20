import { useState } from 'react'
import { postJson } from '../lib/api'
import { useNavigate } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
import LocationFields from './LocationFields'

const initial = {
  clinic_name: '', contact_person: '', email: '', phone: '', website: '',
  country: '', city: '', postal_code: '', address: '', clinic_type: '', specialties: [],
  services: '', number_of_doctors: '', number_of_locations: '1',
  year_established: '', description: '', goals: [],
  preferred_contact_method: 'Email', preferred_language: 'en', consent: false,
}

export default function LeadForm() {
  const { t, i18n } = useTranslation()
  const navigate = useNavigate()
  const [form, setForm] = useState(() => ({
    ...initial,
    preferred_language: i18n.language.startsWith('es') ? 'es' : 'en',
  }))
  const [status, setStatus] = useState({ loading: false, message: '', errors: {}, error: false })

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

  async function submit(event) {
    event.preventDefault()
    setStatus({ loading: true, message: '', errors: {}, error: false })
    try {
      const locale = i18n.language.startsWith('es') ? 'es' : 'en'
      await postJson('/clinic-submissions', { ...form, locale })
      setForm(initial)
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

  const fieldError = name => status.errors[name] ? t('form.validationFieldError') : ''
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

  return (
    <form className="lead-form" onSubmit={submit}>
      {status.message && <div className={status.error ? 'notice error' : 'notice success'} role={status.error ? 'alert' : 'status'}>{status.message}</div>}
      <div className="form-section-title"><h3>{t('form.clinicInfo')}</h3><p>{t('form.clinicInfoText')}</p></div>
      <div className="form-grid">
        <Field label={t('form.clinicNameField')} name="clinic_name" value={form.clinic_name} onChange={update} error={fieldError('clinic_name')} required />
        <Field label={t('form.clinicTypeField')} name="clinic_type" value={form.clinic_type} onChange={update} error={fieldError('clinic_type')} required placeholder={t('form.clinicTypePlaceholder')} />
        <Field label={t('form.websiteField')} name="website" type="url" value={form.website} onChange={update} error={fieldError('website')} placeholder="https://example.com" />
        <Field label={t('form.yearField')} name="year_established" type="number" value={form.year_established} onChange={update} error={fieldError('year_established')} placeholder={t('form.yearPlaceholder')} />
      </div>
      <LocationFields form={form} onChange={update} onCountryChange={updateCountry} fieldError={fieldError} />
      <div className="form-grid">
        <Field label={t('form.addressField')} name="address" value={form.address} onChange={update} error={fieldError('address')} />
        <Field label={t('form.doctorsField')} name="number_of_doctors" value={form.number_of_doctors} onChange={update} placeholder={t('form.doctorsPlaceholder')} />
        <Field label={t('form.locationsField')} name="number_of_locations" value={form.number_of_locations} onChange={update} placeholder={t('form.locationsPlaceholder')} />
      </div>

      <fieldset>
        <legend>{t('form.specialties')}</legend>
        <div className="check-grid">{specialtyOptions.map(([value, label]) =>
          <label className="check-option" key={value}><input type="checkbox" name="specialties" value={value} checked={form.specialties.includes(value)} onChange={updateMulti} />{label}</label>
        )}</div>
      </fieldset>
      <label className="field"><span>{t('form.servicesField')}</span><textarea name="services" rows="3" value={form.services} onChange={update} placeholder={t('form.servicesPlaceholder')} /></label>
      <label className="field"><span>{t('form.aboutField')}</span><textarea name="description" rows="4" value={form.description} onChange={update} placeholder={t('form.aboutPlaceholder')} /></label>

      <div className="form-section-title"><h3>{t('form.primaryContact')}</h3><p>{t('form.primaryContactText')}</p></div>
      <div className="form-grid">
        <Field label={t('form.contactNameField')} name="contact_person" value={form.contact_person} onChange={update} error={fieldError('contact_person')} required />
        <Field label={t('form.businessEmailField')} name="email" type="email" value={form.email} onChange={update} error={fieldError('email')} required />
        <Field label={t('form.phoneField')} name="phone" type="tel" value={form.phone} onChange={update} error={fieldError('phone')} required />
        <label className="field"><span>{t('form.preferredMethod')}</span><select name="preferred_contact_method" value={form.preferred_contact_method} onChange={update}><option value="Email">{t('common.email')}</option><option value="Phone">{t('common.phone')}</option><option value="WhatsApp">WhatsApp</option></select></label>
        <label className="field"><span>{t('form.preferredLanguage')}</span><select name="preferred_language" value={form.preferred_language} onChange={update} required><option value="en">{t('form.languageEnglish')}</option><option value="es">{t('form.languageSpanish')}</option></select></label>
      </div>

      <fieldset>
        <legend>{t('form.goals')}</legend>
        <div className="check-grid">{goalOptions.map(([value, label]) =>
          <label className="check-option" key={value}><input type="checkbox" name="goals" value={value} checked={form.goals.includes(value)} onChange={updateMulti} />{label}</label>
        )}</div>
      </fieldset>
      <label className="check-option consent"><input type="checkbox" name="consent" checked={form.consent} onChange={update} required />{t('form.consent')}</label>
      {fieldError('consent') && <p className="field-error">{fieldError('consent')}</p>}
      <button className="button" type="submit" disabled={status.loading}>{status.loading ? t('form.submitting') : t('form.submitInfo')}</button>
      <p className="form-note">{t('form.formNote')}</p>
    </form>
  )
}

function Field({ label, name, value, onChange, error, type = 'text', ...props }) {
  return <label className="field">
    <span>{label}</span>
    <input name={name} type={type} value={value} onChange={onChange} {...props} />
    {error && <small className="field-error">{error}</small>}
  </label>
}
