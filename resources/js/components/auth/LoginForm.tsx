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
    <div className="w-full max-w-md mx-auto">
      <div className="text-center mb-8">
        <h1 className="text-3xl font-bold text-[#1E293B]">Welcome to LifeOS</h1>
        <p className="text-[#64748B] mt-2">Sign in to your account</p>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <label htmlFor="email" className="block text-sm font-medium text-[#475569] mb-1">
            Email
          </label>
          <Input
            id="email"
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            placeholder="you@example.com"
            required
            error={errors.email}
            disabled={isLoading}
            autoComplete="email"
          />
        </div>

        <div>
          <div className="flex items-center justify-between mb-1">
            <label htmlFor="password" className="block text-sm font-medium text-[#475569]">
              Password
            </label>
            <a href="/forgot-password" className="text-sm text-[#0F766E] hover:underline">
              Forgot password?
            </a>
          </div>
          <Input
            id="password"
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            required
            error={errors.password}
            disabled={isLoading}
            autoComplete="current-password"
          />
        </div>

        {errors.general && (
          <div className="bg-[#fef2f2] border border-[#f87171] p-3 rounded-md text-[#b91c1c] text-sm">
            {errors.general}
          </div>
        )}

        <Button
          type="submit"
          className="w-full"
          disabled={isLoading}
        >
          {isLoading ? "Signing in..." : "Sign in"}
        </Button>
      </form>
    </div>
  )
}
