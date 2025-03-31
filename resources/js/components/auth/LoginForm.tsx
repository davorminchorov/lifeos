import * as React from "react"
import { useState } from "react"
import { Button } from "../ui/button"
import { Input } from "../ui/input"
import axios from "axios"

export function LoginForm() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [errors, setErrors] = useState<Record<string, string>>({})
  const [isLoading, setIsLoading] = useState(false)

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()
    setIsLoading(true)
    setErrors({})

    try {
      const response = await axios.post("/api/login", {
        email,
        password,
      })

      // Redirect on successful login
      window.location.href = "/dashboard"
    } catch (error: any) {
      setIsLoading(false)

      if (error.response?.data?.errors) {
        setErrors(error.response.data.errors)
      } else if (error.response?.data?.message) {
        setErrors({
          general: error.response.data.message
        })
      } else {
        setErrors({
          general: "An unexpected error occurred. Please try again."
        })
      }
    }
  }

  return (
    <div className="w-full">
      <div style={{ textAlign: 'center', marginBottom: '2rem' }}>
        <h1 style={{ color: '#1E293B' }}>Welcome to LifeOS</h1>
        <p style={{ color: '#475569', marginTop: '0.5rem' }}>Sign in to your account</p>
      </div>

      <form onSubmit={handleSubmit} style={{ display: 'flex', flexDirection: 'column', gap: '1rem' }}>
        <div className="form-group">
          <label htmlFor="email" className="form-label">
            Email
          </label>
          <input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@example.com"
            required
            disabled={isLoading}
            autoComplete="email"
            className="form-control"
          />
          {errors.email && (
            <p style={{ color: '#ef4444', fontSize: '0.875rem', marginTop: '0.25rem' }}>{errors.email}</p>
          )}
        </div>

        <div className="form-group">
          <div style={{ display: 'flex', justifyContent: 'space-between', marginBottom: '0.25rem' }}>
            <label htmlFor="password" className="form-label">
              Password
            </label>
            <a href="/forgot-password" style={{ fontSize: '0.875rem', color: '#0F766E' }}>
              Forgot password?
            </a>
          </div>
          <input
            id="password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            disabled={isLoading}
            autoComplete="current-password"
            className="form-control"
          />
          {errors.password && (
            <p style={{ color: '#ef4444', fontSize: '0.875rem', marginTop: '0.25rem' }}>{errors.password}</p>
          )}
        </div>

        {errors.general && (
          <div style={{ backgroundColor: '#fef2f2', border: '1px solid #f87171', padding: '0.75rem', borderRadius: '0.375rem', color: '#b91c1c', fontSize: '0.875rem' }}>
            {errors.general}
          </div>
        )}

        <button
          type="submit"
          className="btn btn-primary btn-block"
          disabled={isLoading}
        >
          {isLoading ? "Signing in..." : "Sign in"}
        </button>
      </form>

      <div style={{ marginTop: '1.5rem', textAlign: 'center', fontSize: '0.875rem', color: '#94A3B8' }}>
        <p>
          Don't have an account?{" "}
          <a href="/register" style={{ color: '#0F766E', fontWeight: 500 }}>
            Create one
          </a>
        </p>
      </div>
    </div>
  )
}
