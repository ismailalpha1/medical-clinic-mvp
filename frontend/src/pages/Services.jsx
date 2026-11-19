import SectionHeading from '../components/SectionHeading'
import { Link } from 'react-router-dom'
import { useTranslation } from 'react-i18next'
export default function Services() {
  const { t } = useTranslation()
  const services = [
    [t('services.primary'), t('services.primaryText')],
    [t('services.diagnostics'), t('services.diagnosticsText')],
    [t('services.dental'), t('services.dentalText')],
    [t('services.specialist'), t('services.specialistText')],
    [t('services.womenChildren'), t('services.womenChildrenText')],
    [t('services.chronic'), t('services.chronicText')],
  ]
  return <section className="section container page-intro">
    <SectionHeading eyebrow={t('services.eyebrow')} title={t('services.introTitle')} text={t('services.introText')} />
    <div className="service-grid service-grid-large">{services.map(([title, desc], i)=><article className="service-card" key={title}><span className="service-number">0{i+1}</span><h3>{title}</h3><p>{desc}</p></article>)}</div>
    <div className="inline-cta"><p>{t('services.moreInfo')}</p><Link className="button" to="/contact">{t('services.talk')}</Link></div>
  </section>
}
