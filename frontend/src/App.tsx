import { useEffect, useState } from 'react'

type ApiStatus = 'checking' | 'ok' | 'unreachable'

function App() {
  const [apiStatus, setApiStatus] = useState<ApiStatus>('checking')

  useEffect(() => {
    fetch('/api/health')
      .then((response) => setApiStatus(response.ok ? 'ok' : 'unreachable'))
      .catch(() => setApiStatus('unreachable'))
  }, [])

  return (
    <main>
      <h1>Jobs Lite</h1>
      <p>API: {apiStatus}</p>
      {/* TODO (candidate): build the job search and job detail pages here */}
    </main>
  )
}

export default App
