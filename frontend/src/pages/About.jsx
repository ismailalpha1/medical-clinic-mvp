import SectionHeading from '../components/SectionHeading'
import { useTranslation } from 'react-i18next'
export default function About() {
  const { t } = useTranslation()
  return <section className="section container page-intro">
    <SectionHeading eyebrow={t('about.eyebrow')} title={t('about.introTitle')} text={t('about.introText')} />
    <div className="split-content"><div className="soft-panel"><span className="panel-symbol">✚</span><h3>{t('about.mission')}</h3><p>{t('about.missionText')}</p></div><div><h2>{t('about.approach')}</h2><p className="muted">{t('about.approachText')}</p><h3>{t('about.values')}</h3><ul className="value-list"><li>{t('about.valueCompassion')}</li><li>{t('about.valueInformation')}</li><li>{t('about.valueProfessionalism')}</li></ul></div></div>
  </section>
}
