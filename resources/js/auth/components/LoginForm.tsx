import * as React from "react"
import { useState } from "react"
import axios from "axios"
import { Button } from "../../ui/Button"
import { Input } from "../../ui/Input"

export function LoginForm() {
  const [email, setEmail] = useState("")
  const [password, setPassword] = useState("")
  const [remember, setRemember] = useState(false)
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
        remember,
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
          <Input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@example.com"
            required
            disabled={isLoading}
            autoComplete="email"
            error={errors.email}
          />
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
          <Input
            id="password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            disabled={isLoading}
            autoComplete="current-password"
            error={errors.password}
          />
        </div>

        <div className="form-group flex items-center">
          <input
            id="remember"
            type="checkbox"
            checked={remember}
            onChange={(e) => setRemember(e.target.checked)}
            className="mr-2 h-4 w-4 text-teal-600 border-gray-300 rounded"
          />
          <label htmlFor="remember" className="text-sm text-slate-600">
            Remember me
          </label>
        </div>

        {errors.general && (
          <div className="rounded-md mb-2" style={{ backgroundColor: '#fef2f2', border: '1px solid #f87171', padding: '0.75rem', color: '#b91c1c', fontSize: '0.875rem' }}>
            {errors.general}
          </div>
        )}

        <Button
          type="submit"
          className="w-full btn btn-primary btn-block"
          disabled={isLoading}
          isLoading={isLoading}
        >
          Sign in
        </Button>
      </form>
    </div>
  )
}
