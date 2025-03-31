import React from "react"
import { LoginForm } from "../components/LoginForm"

export default function Login() {
  return (
    <div className="container">
      <div className="w-full max-w-md">
        <div className="mb-8">
          <div className="w-full h-12 bg-teal-600 flex items-center pl-4 text-white text-lg font-bold">
            L
          </div>
        </div>

        <div className="login-box">
          <LoginForm />
        </div>

        <div className="mt-6 text-center text-xs text-slate-400">
          <p>&copy; {new Date().getFullYear()} LifeOS. All rights reserved.</p>
        </div>
      </div>
    </div>
  )
}
