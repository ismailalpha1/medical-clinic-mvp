import { useEffect, useState } from 'react'
import { useTranslation } from 'react-i18next'

const API_BASE_URL = 'https://countriesnow.space/api/v0.1/countries'

export default function LocationFields({
  form,
  onChange,
  onCountryChange,
  fieldError,
  className = 'form-grid',
}) {
  const { t } = useTranslation()
  const [countries, setCountries] = useState([])
  const [countryStatus, setCountryStatus] = useState({ loading: true, error: false })
  const [countryRetry, setCountryRetry] = useState(0)
  const [cities, setCities] = useState([])
  const [cityStatus, setCityStatus] = useState({ loading: false, error: false })
  const [cityRetry, setCityRetry] = useState(0)

  useEffect(() => {
    const controller = new AbortController()

    async function loadCountries() {
      setCountryStatus({ loading: true, error: false })
      try {
        const response = await fetch(API_BASE_URL, { signal: controller.signal })
        if (!response.ok) throw new Error('Country request failed.')

        const result = await response.json()
        if (result.error || !Array.isArray(result.data)) throw new Error('Invalid country response.')

        const options = result.data
          .map(item => item?.country)
          .filter(country => typeof country === 'string' && country.length > 0)
        if (options.length === 0) throw new Error('Empty country response.')
        setCountries([...new Set(options)].sort((left, right) => left.localeCompare(right)))
        setCountryStatus({ loading: false, error: false })
      } catch {
        if (!controller.signal.aborted) {
          setCountryStatus({ loading: false, error: true })
        }
      }
    }

    loadCountries()
    return () => controller.abort()
  }, [countryRetry])

  useEffect(() => {
    const controller = new AbortController()
    setCities([])

    if (!form.country) {
      setCityStatus({ loading: false, error: false })
      return () => controller.abort()
    }

    async function loadCities() {
      setCityStatus({ loading: true, error: false })
      try {
        const response = await fetch(`${API_BASE_URL}/cities`, {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ country: form.country }),
          signal: controller.signal,
        })
        if (!response.ok) throw new Error('City request failed.')

        const result = await response.json()
        if (result.error || !Array.isArray(result.data)) throw new Error('Invalid city response.')

        const options = result.data.filter(city => typeof city === 'string' && city.length > 0)
        setCities([...new Set(options)].sort((left, right) => left.localeCompare(right)))
        setCityStatus({ loading: false, error: false })
      } catch {
        if (!controller.signal.aborted) {
          setCityStatus({ loading: false, error: true })
        }
      }
    }

    loadCities()
    return () => controller.abort()
  }, [form.country, cityRetry])

  return (
    <div className={className}>
      <div className="field">
        <label htmlFor="clinic-country">{t('form.countryField')}</label>
        <select
          id="clinic-country"
          name="country"
          value={form.country}
          onChange={onCountryChange}
          required
          aria-busy={countryStatus.loading}
        >
          <option value="">
            {countryStatus.loading ? t('form.loadingCountries') : t('form.selectCountry')}
          </option>
          {countries.map(country => <option key={country} value={country}>{country}</option>)}
        </select>
        {fieldError('country') && <small className="field-error">{fieldError('country')}</small>}
        {countryStatus.error && (
          <small className="location-api-message" role="alert">
            {t('form.countryLoadError')}{' '}
            <button type="button" className="location-retry" onClick={() => setCountryRetry(value => value + 1)}>
              {t('form.retry')}
            </button>
          </small>
        )}
      </div>

      <div className="field">
        <label htmlFor="clinic-city">{t('form.cityField')}</label>
        <select
          id="clinic-city"
          name="city"
          value={form.city}
          onChange={onChange}
          required
          aria-busy={cityStatus.loading}
        >
          <option value="">
            {!form.country
              ? t('form.selectCountryFirst')
              : cityStatus.loading
                ? t('form.loadingCities')
                : t('form.selectCity')}
          </option>
          {cities.map(city => <option key={city} value={city}>{city}</option>)}
        </select>
        {fieldError('city') && <small className="field-error">{fieldError('city')}</small>}
        {cityStatus.error && (
          <small className="location-api-message" role="alert">
            {t('form.cityLoadError')}{' '}
            <button type="button" className="location-retry" onClick={() => setCityRetry(value => value + 1)}>
              {t('form.retry')}
            </button>
          </small>
        )}
        {form.country && !cityStatus.loading && !cityStatus.error && cities.length === 0 && (
          <small className="location-api-message">{t('form.noCities')}</small>
        )}
      </div>

      <label className="field">
        <span>{t('form.postalCodeField')}</span>
        <input name="postal_code" value={form.postal_code} onChange={onChange} />
        {fieldError('postal_code') && <small className="field-error">{fieldError('postal_code')}</small>}
      </label>
    </div>
  )
}
