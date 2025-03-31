import * as React from "react"
import { useState } from "react"
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
      <div className="text-center mb-8">
        <h1 className="text-slate-800">Welcome to LifeOS</h1>
        <p className="text-slate-600 mt-2">Sign in to your account</p>
      </div>

      <form onSubmit={handleSubmit} className="w-full">
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
            <p className="text-sm mt-1" style={{ color: '#ef4444' }}>{errors.email}</p>
          )}
        </div>

        <div className="form-group">
          <div className="flex justify-between mb-1">
            <label htmlFor="password" className="form-label">
              Password
            </label>
            <a href="/forgot-password" className="text-sm text-teal-600">
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
            <p className="text-sm mt-1" style={{ color: '#ef4444' }}>{errors.password}</p>
          )}
        </div>

        {errors.general && (
          <div className="rounded-md mb-2" style={{ backgroundColor: '#fef2f2', border: '1px solid #f87171', padding: '0.75rem', color: '#b91c1c', fontSize: '0.875rem' }}>
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

      <div className="mt-6 text-center text-sm text-slate-400">
        <p>
          Don't have an account?{" "}
          <a href="/register" className="text-teal-600 font-medium">
            Create one
          </a>
        </p>
      </div>
    </div>
  )
}
