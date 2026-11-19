import { Link } from 'react-router-dom'
import SectionHeading from '../components/SectionHeading'
import LeadForm from '../components/LeadForm'
import { useTranslation } from 'react-i18next'

export default function ClinicRegistration() {
  const { t } = useTranslation()

  return <section className="section container page-intro">
    <SectionHeading eyebrow={t('form.pageEyebrow')} title={t('form.pageTitle')} text={t('form.pageText')} />
    <p className="wizard-switch"><Link className="text-link" to="/register-clinic-wizard">{t('wizard.tryLink')} →</Link></p>
    <div className="registration-layout">
      <div className="registration-aside">
        <h3>{t('form.nextTitle')}</h3>
        <ol>
          <li>{t('form.nextStepOne')}</li>
          <li>{t('form.nextStepTwo')}</li>
          <li>{t('form.nextStepThree')}</li>
        </ol>
        <p className="form-note">{t('form.businessOnlyNote')}</p>
        <Link className="text-link" to="/contact">{t('form.generalQuestion')} →</Link>
      </div>
      <LeadForm />
    </div>
  </section>
}
