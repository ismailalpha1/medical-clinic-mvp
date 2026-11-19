const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api'

export async function getJson(path) {
  let response
  try {
    response = await fetch(`${API_BASE_URL}${path}`, {
      headers: { Accept: 'application/json' },
    })
  } catch (cause) {
    const error = new Error('Unable to connect to the clinic API.')
    error.code = 'NETWORK_ERROR'
    error.cause = cause
    throw error
  }

  const data = await response.json().catch(() => ({}))
  if (!response.ok) {
    const error = new Error(data.message || 'Something went wrong. Please try again.')
    error.status = response.status
    error.code = data.code
    error.errors = data.errors || {}
    throw error
  }
  return data
}

export async function postJson(path, payload) {
  let response
  try {
    response = await fetch(`${API_BASE_URL}${path}`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(payload),
    })
  } catch (cause) {
    const error = new Error('Unable to connect to the clinic API.')
    error.code = 'NETWORK_ERROR'
    error.cause = cause
    throw error
  }

  const data = await response.json().catch(() => ({}))
  if (!response.ok) {
    const error = new Error(data.message || 'Something went wrong. Please try again.')
    error.status = response.status
    error.code = data.code
    error.errors = data.errors || {}
    throw error
  }
  return data
}
