import React from "react"
import { LoginForm } from "../../components/auth/LoginForm"

export default function Login() {
  return (
    <div className="container">
      <div className="w-full max-w-md">
        <div className="mb-8 flex justify-center">
          {/* Logo */}
          <div className="w-12 h-12 rounded-full bg-teal-600 flex items-center justify-center text-white text-lg font-bold" style={{ backgroundColor: "#0F766E" }}>
            L
          </div>
        </div>

        <div className="login-box">
          <LoginForm />
        </div>

        <div className="mt-8 text-center text-xs text-slate-400" style={{ color: "#94A3B8" }}>
          <p>&copy; {new Date().getFullYear()} LifeOS. All rights reserved.</p>
        </div>
      </div>
    </div>
  )
}
