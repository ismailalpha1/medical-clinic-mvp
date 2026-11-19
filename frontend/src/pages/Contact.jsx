import { useState } from 'react'
import { postJson } from '../lib/api'
import SectionHeading from '../components/SectionHeading'
import { useTranslation } from 'react-i18next'

export default function Contact() {
  const { t, i18n } = useTranslation()
  const [form, setForm] = useState({ name: '', email: '', phone: '', subject: '', message: '', consent: false })
  const [status, setStatus] = useState({ loading: false, message: '', error: false })
  function update(e) { const { name, value, checked, type } = e.target; setForm(s => ({ ...s, [name]: type === 'checkbox' ? checked : value })) }
  async function submit(e) {
    e.preventDefault(); setStatus({ loading: true, message: '', error: false })
    try { const locale = i18n.language.startsWith('es') ? 'es' : 'en'; const data = await postJson('/contact-inquiries', { ...form, locale }); setStatus({ loading: false, message: data.message || t('contact.sentFallback'), error: false }); setForm({ name: '', email: '', phone: '', subject: '', message: '', consent: false }) }
    catch (err) { setStatus({ loading: false, message: err.message, error: true }) }
  }
  return <section className="section container page-intro">
    <SectionHeading eyebrow={t('contact.eyebrow')} title={t('contact.introTitle')} text={t('contact.introText')} />
    <div className="contact-layout"><div className="contact-details"><h3>{t('contact.getInTouch')}</h3><p className="muted">{t('contact.demoDetails')}</p><p><strong>{t('common.email')}</strong><br/>hello@exampleclinic.com</p><p><strong>{t('common.phone')}</strong><br/>+1 555 010 2020</p><p><strong>{t('common.address')}</strong><br/>{t('contact.demoAddress')}</p></div>
    <form className="lead-form compact-form" onSubmit={submit}>
      {status.message && <div className={status.error ? 'notice error' : 'notice success'} role="status">{status.message}</div>}
      <div className="form-grid"><Field label={t('contact.nameField')} name="name" value={form.name} onChange={update} required/><Field label={t('contact.emailField')} name="email" type="email" value={form.email} onChange={update} required/><Field label={t('contact.phoneField')} name="phone" type="tel" value={form.phone} onChange={update}/><Field label={t('contact.subjectField')} name="subject" value={form.subject} onChange={update} required/></div>
      <label className="field"><span>{t('contact.messageField')}</span><textarea name="message" rows="5" value={form.message} onChange={update} required maxLength="5000"/></label>
      <label className="check-option consent"><input type="checkbox" name="consent" checked={form.consent} onChange={update} required/>{t('contact.consent')}</label>
      <button className="button" disabled={status.loading}>{status.loading ? t('contact.sending') : t('contact.submit')}</button>
      <p className="form-note">{t('contact.formNote')}</p>
    </form></div>
  </section>
}
function Field({ label, name, value, onChange, type = 'text', ...props }) {
  return <label className="field"><span>{label}</span><input name={name} type={type} value={value} onChange={onChange} {...props}/></label>
}
