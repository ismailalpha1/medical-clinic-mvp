import { Link } from 'react-router-dom'
import SectionHeading from '../components/SectionHeading'
import { useTranslation } from 'react-i18next'

export default function Home() {
  const { t } = useTranslation()
  const serviceNames = [
    t('services.primary'),
    t('services.diagnostics'),
    t('services.dental'),
  ]

  return <>
    <section className="hero">
      <div className="container hero-grid">
        <div className="hero-copy">
          <span className="eyebrow">{t('home.heroEyebrow')}</span>
          <h1>{t('home.heroTitleStart')}<em>{t('home.heroTitleEmphasis')}</em></h1>
          <p>{t('home.heroDescription')}</p>
          <div className="hero-actions"><Link className="button" to="/services">{t('home.exploreServices')}</Link><Link className="text-link" to="/about">{t('home.getToKnowUs')} →</Link></div>
          <div className="hero-proof"><strong>{t('home.professionalCare')}</strong><span>{t('home.professionalCareText')}</span></div>
        </div>
        <div className="hero-art"><div className="hero-circle">+</div><div className="hero-card"><span>{t('home.priorityEyebrow')}</span><strong>{t('home.priorityTitle')}</strong><small>{t('home.priorityValues')}</small></div></div>
      </div>
    </section>
    <section className="section container">
      <SectionHeading eyebrow={t('home.whoEyebrow')} title={t('home.whoTitle')} text={t('home.whoText')} />
      <div className="split-content"><div className="soft-panel"><span className="panel-symbol">✚</span><h3>{t('home.confidenceTitle')}</h3><p>{t('home.confidenceText')}</p></div><div><h3>{t('home.connectTitle')}</h3><p className="muted">{t('home.connectText')}</p><Link className="text-link" to="/about">{t('home.moreAboutUs')} →</Link></div></div>
    </section>
    <section className="section section-tint"><div className="container"><SectionHeading eyebrow={t('home.serviceExploreEyebrow')} title={t('home.serviceExploreTitle')} text={t('home.serviceExploreText')}/><div className="service-grid">{serviceNames.map((name,i)=><article className="service-card" key={name}><span className="service-number">0{i+1}</span><h3>{name}</h3><p>{t('home.serviceSupportText')}</p><Link to="/services" className="text-link">{t('home.learnMore')} →</Link></article>)}</div></div></section>
    <section className="section container contact-cta"><div><span className="eyebrow">{t('home.connectEyebrow')}</span><h2>{t('home.questionTitle')}</h2><p className="muted">{t('home.questionText')}</p></div><Link className="button" to="/contact">{t('home.contactUs')}</Link></section>
  </>
}
