import { useRef, useState } from 'react'
import { useTranslation } from 'react-i18next'
import { getJson, postJson } from '../lib/api'

const questionFields = ['name', 'contact', 'need', 'location', 'method', 'time', 'reason']
const steps = {
  booking: 7,
  meetingType: 8,
  appointmentLocation: 9,
  appointmentDate: 10,
  appointmentTime: 11,
  review: 12,
}

const emptyAnswers = {
  name: '',
  contact: '',
  need: '',
  location: '',
  method: '',
  time: '',
  reason: '',
  bookAppointment: false,
  meetingType: '',
  appointmentLocation: '',
  appointmentDate: '',
  appointmentStart: '',
  idempotencyKey: '',
}

function makeIdempotencyKey() {
  if (typeof crypto.randomUUID === 'function') return crypto.randomUUID()
  const bytes = crypto.getRandomValues(new Uint8Array(16))
  bytes[6] = (bytes[6] & 0x0f) | 0x40
  bytes[8] = (bytes[8] & 0x3f) | 0x80
  const hex = Array.from(bytes, byte => byte.toString(16).padStart(2, '0')).join('')
  return `${hex.slice(0, 8)}-${hex.slice(8, 12)}-${hex.slice(12, 16)}-${hex.slice(16, 20)}-${hex.slice(20)}`
}

function localDateString(date) {
  const year = date.getFullYear()
  const month = String(date.getMonth() + 1).padStart(2, '0')
  const day = String(date.getDate()).padStart(2, '0')
  return `${year}-${month}-${day}`
}

export default function ChatbotWidget() {
  const { t, i18n } = useTranslation()
  const [open, setOpen] = useState(false)
  const [started, setStarted] = useState(false)
  const [step, setStep] = useState(0)
  const [answers, setAnswers] = useState(emptyAnswers)
  const [value, setValue] = useState('')
  const [consent, setConsent] = useState(false)
  const [status, setStatus] = useState({ loading: false, error: '', success: false })
  const [availability, setAvailability] = useState({ loading: false, error: '', slots: [], timezone: '', durationMinutes: 0 })
  const [appointmentResult, setAppointmentResult] = useState(null)
  const availabilityRequestId = useRef(0)

  const needOptions = [
    ['appointment', t('chatbot.needAppointment')],
    ['services', t('chatbot.needServices')],
    ['location', t('chatbot.needLocation')],
    ['callback', t('chatbot.needCallback')],
    ['other', t('chatbot.needOther')],
  ]
  const contactIsEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(answers.contact)
  const methodOptions = contactIsEmail
    ? [['email', t('chatbot.methodEmail')]]
    : [
        ['phone', t('chatbot.methodPhone')],
        ['whatsapp', t('chatbot.methodWhatsapp')],
      ]

  function answerLabel(index) {
    const field = questionFields[index]
    const answer = answers[field]
    if (!answer) return t('chatbot.skipped')
    if (field === 'need') {
      return needOptions.find(([key]) => key === answer)?.[1] || answer
    }
    if (field === 'method') {
      return methodOptions.find(([key]) => key === answer)?.[1] || answer
    }
    return answer
  }

  function advance(field, answer) {
    setAnswers(current => ({ ...current, [field]: answer }))
    setValue('')
    setStatus({ loading: false, error: '', success: false })
    setStep(current => current + 1)
  }

  function submitAnswer(event) {
    event.preventDefault()
    const answer = value.trim()

    if (step === 0 && !answer) {
      setStatus({ loading: false, error: t('chatbot.nameRequired'), success: false })
      return
    }

    if (step === 1) {
      const emailValid = /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(answer)
      const phoneDigits = answer.replace(/\D/g, '')
      const phoneValid = /^[+]?[\d\s().-]+$/.test(answer) && phoneDigits.length >= 7
      if (!emailValid && !phoneValid) {
        setStatus({ loading: false, error: t('chatbot.contactRequired'), success: false })
        return
      }
    }

    advance(questionFields[step], answer)
  }

  function choose(field, answer) {
    advance(field, answer)
  }

  function chooseBooking(book) {
    availabilityRequestId.current += 1
    setAnswers(current => ({
      ...current,
      bookAppointment: book,
      meetingType: '',
      appointmentLocation: '',
      appointmentDate: '',
      appointmentStart: '',
      idempotencyKey: '',
    }))
    setAvailability({ loading: false, error: '', slots: [], timezone: '', durationMinutes: 0 })
    setStatus({ loading: false, error: '', success: false })
    setStep(book ? steps.meetingType : steps.review)
  }

  function chooseMeetingType(meetingType) {
    availabilityRequestId.current += 1
    if (meetingType === 'google_meet' && !contactIsEmail) {
      setStatus({ loading: false, error: t('chatbot.emailRequiredForVideo'), success: false })
      return
    }

    const appointmentLocation = meetingType === 'clinic' ? answers.location : ''
    setAnswers(current => ({ ...current, meetingType, appointmentLocation }))
    setStatus({ loading: false, error: '', success: false })
    setStep(meetingType === 'clinic' && !appointmentLocation ? steps.appointmentLocation : steps.appointmentDate)
  }

  function submitAppointmentLocation(event) {
    event.preventDefault()
    const location = value.trim()
    if (!location) {
      setStatus({ loading: false, error: t('chatbot.appointmentLocationRequired'), success: false })
      return
    }

    setAnswers(current => ({ ...current, appointmentLocation: location }))
    setValue('')
    setStatus({ loading: false, error: '', success: false })
    setStep(steps.appointmentDate)
  }

  async function loadDateSlots(date) {
    const requestId = ++availabilityRequestId.current
    setAnswers(current => ({ ...current, appointmentDate: date, appointmentStart: '', idempotencyKey: '' }))
    setAvailability({ loading: Boolean(date), error: '', slots: [], timezone: '', durationMinutes: 0 })
    if (!date) return

    try {
      const result = await getJson(
        `/appointments/availability?date=${encodeURIComponent(date)}&need=${encodeURIComponent(answers.need)}`
      )
      if (requestId !== availabilityRequestId.current) return
      setAvailability({
        loading: false,
        error: '',
        slots: Array.isArray(result.slots) ? result.slots : [],
        timezone: result.timezone,
        durationMinutes: result.duration_minutes,
      })
    } catch {
      if (requestId !== availabilityRequestId.current) return
      setAvailability({
        loading: false,
        error: t('chatbot.availabilityError'),
        slots: [],
        timezone: '',
        durationMinutes: 0,
      })
    }
  }

  function chooseSlot(slot) {
    setAnswers(current => ({
      ...current,
      appointmentStart: slot.start_at,
      idempotencyKey: makeIdempotencyKey(),
    }))
    setStatus({ loading: false, error: '', success: false })
    setStep(steps.review)
  }

  function continueWithoutBooking() {
    availabilityRequestId.current += 1
    setAnswers(current => ({
      ...current,
      bookAppointment: false,
      meetingType: '',
      appointmentLocation: '',
      appointmentDate: '',
      appointmentStart: '',
      idempotencyKey: '',
    }))
    setAvailability({ loading: false, error: '', slots: [], timezone: '', durationMinutes: 0 })
    setStatus({ loading: false, error: '', success: false })
    setStep(steps.review)
  }

  function goBack() {
    if (step === 0) {
      setStarted(false)
      return
    }

    if (step === steps.review) {
      setStep(answers.bookAppointment ? steps.appointmentTime : steps.booking)
      setStatus({ loading: false, error: '', success: false })
      return
    }
    if (step === steps.appointmentTime) {
      setStep(steps.appointmentDate)
      return
    }
    if (step === steps.appointmentDate) {
      availabilityRequestId.current += 1
      setStep(answers.meetingType === 'clinic' && !answers.location ? steps.appointmentLocation : steps.meetingType)
      return
    }
    if (step === steps.appointmentLocation || step === steps.meetingType) {
      setStep(step === steps.appointmentLocation ? steps.meetingType : steps.booking)
      return
    }

    const previousStep = step - 1
    setStep(previousStep)
    setValue(answers[questionFields[previousStep]] || '')
    setStatus({ loading: false, error: '', success: false })
  }

  function restart() {
    availabilityRequestId.current += 1
    setAnswers(emptyAnswers)
    setStep(0)
    setValue('')
    setConsent(false)
    setAvailability({ loading: false, error: '', slots: [], timezone: '', durationMinutes: 0 })
    setAppointmentResult(null)
    setStatus({ loading: false, error: '', success: false })
  }

  async function submitRequest(event) {
    event.preventDefault()
    if (!consent) {
      setStatus({ loading: false, error: t('chatbot.consentRequired'), success: false })
      return
    }

    const email = contactIsEmail ? answers.contact : null
    const phone = contactIsEmail ? null : answers.contact
    const message = [
      `${t('chatbot.summaryNeed')}: ${answerLabel(2)}`,
      `${t('chatbot.summaryLocation')}: ${answers.location || t('chatbot.skipped')}`,
      `${t('chatbot.summaryMethod')}: ${answerLabel(4)}`,
      `${t('chatbot.summaryTime')}: ${answers.time || t('chatbot.skipped')}`,
    ].join('\n')
    const locale = i18n.language.startsWith('es') ? 'es' : 'en'

    setStatus({ loading: true, error: '', success: false })
    try {
      if (answers.bookAppointment) {
        const result = await postJson('/appointments', {
          name: answers.name,
          email,
          phone,
          need: answers.need,
          meeting_type: answers.meetingType,
          location: answers.meetingType === 'clinic' ? answers.appointmentLocation : null,
          start_at: answers.appointmentStart,
          idempotency_key: answers.idempotencyKey,
          visit_reason: answers.reason || null,
          consent: true,
          locale,
        })
        setAppointmentResult(result.appointment)
      } else {
        await postJson('/contact-inquiries', {
          name: answers.name,
          email,
          phone,
          subject: t('chatbot.subject'),
          message,
          visit_reason: answers.reason || null,
          consent: true,
          locale,
        })
      }
      setStatus({ loading: false, error: '', success: true })
      setStep(steps.review + 1)
    } catch (error) {
      const errorMessage = error.code === 'email_required_for_video'
        ? t('chatbot.emailRequiredForVideo')
        : answers.bookAppointment
          ? t('chatbot.bookingUnavailable')
          : t('chatbot.error')
      setStatus({ loading: false, error: errorMessage, success: false })
    }
  }

  const questions = [
    t('chatbot.askName'),
    t('chatbot.askContact'),
    t('chatbot.askNeed'),
    t('chatbot.askLocation'),
    t('chatbot.askMethod'),
    t('chatbot.askTime'),
    t('chatbot.askReason'),
  ]
  const historyItems = questions.slice(0, Math.min(step, questions.length)).map((question, index) => ({
    key: questionFields[index],
    question,
    answer: answerLabel(index),
  }))
  if (step > steps.booking) {
    historyItems.push({
      key: 'bookAppointment',
      question: t('chatbot.askBooking'),
      answer: answers.bookAppointment ? t('chatbot.bookNow') : t('chatbot.noBooking'),
    })
  }
  if (answers.bookAppointment && step > steps.meetingType) {
    historyItems.push({
      key: 'meetingType',
      question: t('chatbot.askMeetingType'),
      answer: answers.meetingType === 'google_meet' ? t('chatbot.meetingGoogle') : t('chatbot.meetingClinic'),
    })
  }
  if (answers.bookAppointment && answers.meetingType === 'clinic' && step > steps.appointmentLocation) {
    historyItems.push({
      key: 'appointmentLocation',
      question: t('chatbot.askAppointmentLocation'),
      answer: answers.appointmentLocation,
    })
  }
  if (answers.bookAppointment && step > steps.appointmentDate) {
    historyItems.push({
      key: 'appointmentDate',
      question: t('chatbot.askAppointmentDate'),
      answer: answers.appointmentDate,
    })
  }
  if (answers.bookAppointment && step > steps.appointmentTime) {
    historyItems.push({
      key: 'appointmentStart',
      question: t('chatbot.askAppointmentTime'),
      answer: answers.appointmentStart
        ? new Intl.DateTimeFormat(i18n.language, {
            dateStyle: 'medium',
            timeStyle: 'short',
            timeZone: availability.timezone || undefined,
          }).format(new Date(answers.appointmentStart))
        : t('chatbot.skipped'),
    })
  }
  const appointmentSummary = historyItems.find(item => item.key === 'appointmentStart')?.answer

  return (
    <aside className="chatbot-widget" aria-label={t('chatbot.title')}>
      {open && (
        <section className="chatbot-panel" role="dialog" aria-label={t('chatbot.title')}>
          <header className="chatbot-header">
            <span className="chatbot-avatar" aria-hidden="true">+</span>
            <div className="chatbot-header-copy">
              <strong>{t('chatbot.title')}</strong>
              <small>CarePoint</small>
            </div>
            <button type="button" className="chatbot-close" aria-label={t('chatbot.close')} onClick={() => setOpen(false)}>×</button>
          </header>

          <div className="chatbot-transcript" role="log" aria-live="polite">
            <div className="chatbot-bubble chatbot-bot">{t('chatbot.greeting')}</div>
            <p className="chatbot-privacy">{t('chatbot.privacy')}</p>

            {!started && (
              <button type="button" className="button chatbot-start" onClick={() => setStarted(true)}>
                {t('chatbot.start')}
              </button>
            )}

            {started && !status.success && (
              <>
                {historyItems.map(item => (
                  <div className="chatbot-history" key={item.key}>
                    <div className="chatbot-bubble chatbot-bot">{item.question}</div>
                    <div className="chatbot-bubble chatbot-user">{item.answer}</div>
                  </div>
                ))}

                {step < questions.length && (
                  <div className="chatbot-bubble chatbot-bot">{questions[step]}</div>
                )}

                {step === steps.booking && <div className="chatbot-bubble chatbot-bot">{t('chatbot.askBooking')}</div>}
                {step === steps.meetingType && <div className="chatbot-bubble chatbot-bot">{t('chatbot.askMeetingType')}</div>}
                {step === steps.appointmentLocation && <div className="chatbot-bubble chatbot-bot">{t('chatbot.askAppointmentLocation')}</div>}
                {step === steps.appointmentDate && <div className="chatbot-bubble chatbot-bot">{t('chatbot.askAppointmentDate')}</div>}
                {step === steps.appointmentTime && <div className="chatbot-bubble chatbot-bot">{t('chatbot.askAppointmentTime')}</div>}

                {step === steps.review && (
                  <>
                    <div className="chatbot-bubble chatbot-bot">{t('chatbot.reviewPrompt')}</div>
                    <div className="chatbot-summary">
                      {questionFields.map((field, index) => (
                        <p key={field}><strong>{questions[index]}</strong><span>{answerLabel(index)}</span></p>
                      ))}
                      {answers.bookAppointment && (
                        <p>
                          <strong>{t('chatbot.appointmentTimeSummary')}</strong>
                          <span>{appointmentSummary}</span>
                        </p>
                      )}
                    </div>
                    <form className="chatbot-consent" onSubmit={submitRequest}>
                      <label className="check-option">
                        <input type="checkbox" checked={consent} onChange={event => setConsent(event.target.checked)} />
                        {t('chatbot.consent')}
                      </label>
                      <button className="button" type="submit" disabled={status.loading}>
                        {status.loading
                          ? t('chatbot.submitting')
                          : answers.bookAppointment
                            ? t('chatbot.confirmBooking')
                            : t('chatbot.submit')}
                      </button>
                    </form>
                  </>
                )}
              </>
            )}

            {status.success && (
              <div className="chatbot-success">
                <span className="chatbot-success-mark" aria-hidden="true">✓</span>
                <strong>{t('chatbot.successTitle')}</strong>
                <p>{answers.bookAppointment ? t('chatbot.bookingSuccessMessage') : t('chatbot.inquirySuccessMessage')}</p>
                {appointmentResult?.meet_link && (
                  <a className="button chatbot-meet-link" href={appointmentResult.meet_link} target="_blank" rel="noreferrer">
                    {t('chatbot.meetLink')}
                  </a>
                )}
                <button type="button" className="text-link" onClick={restart}>{t('chatbot.restart')}</button>
              </div>
            )}

            {status.error && <p className="chatbot-error" role="alert">{status.error}</p>}
          </div>

          {started && !status.success && step < steps.review && (
            <div className="chatbot-controls">
              {step === 2 ? (
                <div className="chatbot-options">
                  {needOptions.map(([key, label]) => (
                    <button type="button" key={key} className="chatbot-option" onClick={() => choose('need', key)}>{label}</button>
                  ))}
                </div>
              ) : step === 4 ? (
                <>
                  <p className="chatbot-contact-prompt">{t('chatbot.contactPrompt')}</p>
                  <div className="chatbot-options">
                    {methodOptions.map(([key, label]) => (
                      <button type="button" key={key} className="chatbot-option" onClick={() => choose('method', key)}>{label}</button>
                    ))}
                  </div>
                </>
              ) : step === steps.booking ? (
                <div className="chatbot-options">
                  <button type="button" className="chatbot-option" onClick={() => chooseBooking(true)}>{t('chatbot.bookNow')}</button>
                  <button type="button" className="chatbot-option" onClick={() => chooseBooking(false)}>{t('chatbot.noBooking')}</button>
                </div>
              ) : step === steps.meetingType ? (
                <>
                  <div className="chatbot-options">
                    <button type="button" className="chatbot-option" onClick={() => chooseMeetingType('clinic')}>{t('chatbot.meetingClinic')}</button>
                    {contactIsEmail && (
                      <button type="button" className="chatbot-option" onClick={() => chooseMeetingType('google_meet')}>{t('chatbot.meetingGoogle')}</button>
                    )}
                  </div>
                  {!contactIsEmail && <p className="chatbot-input-hint">{t('chatbot.emailRequiredForVideo')}</p>}
                </>
              ) : step === steps.appointmentLocation ? (
                <form className="chatbot-answer-form" onSubmit={submitAppointmentLocation}>
                  <input
                    aria-label={t('chatbot.askAppointmentLocation')}
                    type="text"
                    value={value}
                    onChange={event => {
                      setValue(event.target.value)
                      if (status.error) setStatus(current => ({ ...current, error: '' }))
                    }}
                    placeholder={t('chatbot.placeholderLocation')}
                    maxLength="180"
                  />
                  <div className="chatbot-control-actions">
                    <button type="button" className="chatbot-back" onClick={goBack}>{t('chatbot.back')}</button>
                    <button type="submit" className="button">{t('chatbot.next')}</button>
                  </div>
                </form>
              ) : step === steps.appointmentDate ? (
                <div className="chatbot-date-control">
                  {availability.timezone && (
                    <p className="chatbot-input-hint">
                      {t('chatbot.durationNotice', {
                        timezone: availability.timezone,
                        minutes: availability.durationMinutes,
                      })}
                    </p>
                  )}
                  <input
                    type="date"
                    aria-label={t('chatbot.askAppointmentDate')}
                    value={answers.appointmentDate}
                    min={localDateString(new Date())}
                    max={localDateString(new Date(Date.now() + 60 * 24 * 60 * 60 * 1000))}
                    onChange={event => loadDateSlots(event.target.value)}
                  />
                  {availability.loading && <p className="chatbot-input-hint">{t('chatbot.loadingSlots')}</p>}
                  {availability.error && <p className="chatbot-error" role="alert">{availability.error}</p>}
                  <div className="chatbot-control-actions">
                    <button type="button" className="chatbot-back" onClick={goBack}>{t('chatbot.back')}</button>
                    <button
                      type="button"
                      className="button"
                      disabled={!answers.appointmentDate || availability.loading || Boolean(availability.error)}
                      onClick={() => setStep(steps.appointmentTime)}
                    >
                      {t('chatbot.next')}
                    </button>
                  </div>
                </div>
              ) : step === steps.appointmentTime ? (
                <div className="chatbot-date-control">
                  {availability.timezone && (
                    <p className="chatbot-input-hint">
                      {t('chatbot.durationNotice', {
                        timezone: availability.timezone,
                        minutes: availability.durationMinutes,
                      })}
                    </p>
                  )}
                  {availability.loading && <p className="chatbot-input-hint">{t('chatbot.loadingSlots')}</p>}
                  {availability.error && <p className="chatbot-error" role="alert">{availability.error}</p>}
                  {!availability.loading && !availability.error && availability.slots.length === 0 && (
                    <p className="chatbot-input-hint">{t('chatbot.noSlots')}</p>
                  )}
                  <div className="chatbot-options chatbot-slot-options">
                    {availability.slots.map(slot => (
                      <button type="button" key={slot.start_at} className="chatbot-option" onClick={() => chooseSlot(slot)}>
                        {slot.time}
                      </button>
                    ))}
                  </div>
                  <div className="chatbot-control-actions">
                    <button type="button" className="chatbot-back" onClick={goBack}>{t('chatbot.back')}</button>
                  </div>
                </div>
              ) : (
                <form className="chatbot-answer-form" onSubmit={submitAnswer}>
                  {step === 6 ? (
                    <><textarea
                          aria-label={questions[step]}
                          value={value}
                          onChange={event => {
                            setValue(event.target.value)
                            if (status.error) setStatus(current => ({ ...current, error: '' }))
                          } }
                          placeholder={t('chatbot.placeholderReason')}
                          rows="3"
                          maxLength="300" /><small className="chatbot-input-hint">{t('chatbot.reasonHint')}</small></>
                  ) : (
                    <input
                      aria-label={questions[step]}
                      type="text"
                      value={value}
                      onChange={event => {
                        setValue(event.target.value)
                        if (status.error) setStatus(current => ({ ...current, error: '' }))
                      }}
                      placeholder={step === 0 ? t('chatbot.placeholderName') : step === 1 ? t('chatbot.placeholderContact') : step === 3 ? t('chatbot.placeholderLocation') : t('chatbot.placeholderTime')}
                      maxLength={step === 1 ? 180 : 160}
                      autoComplete={step === 0 ? 'name' : step === 1 ? 'email' : 'off'}
                    />
                  )}
                  <div className="chatbot-control-actions">
                    {step > 0 && <button type="button" className="chatbot-back" onClick={goBack}>{t('chatbot.back')}</button>}
                    <button type="submit" className="button">{t('chatbot.next')}</button>
                  </div>
                  {(step === 3 || step === 5 || step === 6) && (
                    <button type="button" className="chatbot-skip" onClick={() => advance(questionFields[step], '')}>{t('chatbot.skip')}</button>
                  )}
                </form>
              )}
              {step === 2 || step === 4 || step === steps.booking || step === steps.meetingType ? (
                <button type="button" className="chatbot-back" onClick={goBack}>{t('chatbot.back')}</button>
              ) : null}
              {step >= steps.meetingType && step <= steps.appointmentTime && (
                <button type="button" className="chatbot-skip" onClick={continueWithoutBooking}>{t('chatbot.bookWithoutCalendar')}</button>
              )}
            </div>
          )}

          {started && !status.success && step === steps.review && (
            <div className="chatbot-final-back">
              <button type="button" className="chatbot-back" onClick={goBack}>{t('chatbot.back')}</button>
            </div>
          )}
        </section>
      )}

      <button
        type="button"
        className={`chatbot-launcher${open ? ' is-open' : ''}`}
        aria-expanded={open}
        aria-label={open ? t('chatbot.close') : t('chatbot.launcher')}
        onClick={() => setOpen(current => !current)}
      >
        {open ? <span aria-hidden="true">×</span> : <><span className="chatbot-launcher-icon" aria-hidden="true">✦</span><span>{t('chatbot.launcher')}</span></>}
      </button>
    </aside>
  )
}
