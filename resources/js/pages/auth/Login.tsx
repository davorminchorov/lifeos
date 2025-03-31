import React from "react"
import { LoginForm } from "../../components/auth/LoginForm"

export default function Login() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] flex flex-col justify-center items-center p-4">
      <div className="w-full max-w-md">
        <div className="mb-8 flex justify-center">
          {/* Logo */}
          <div className="w-12 h-12 rounded-full bg-[#0F766E] flex items-center justify-center text-white text-lg font-bold">
            L
          </div>
        </div>

        <div className="bg-white shadow-md rounded-lg p-8">
          <LoginForm />

          <div className="mt-6 text-center text-sm text-[#64748B]">
            <p>
              Don't have an account?{" "}
              <a href="/register" className="text-[#0F766E] hover:underline font-medium">
                Create one
              </a>
            </p>
          </div>
        </div>

        <div className="mt-8 text-center text-xs text-[#94A3B8]">
          <p>&copy; {new Date().getFullYear()} LifeOS. All rights reserved.</p>
        </div>
      </div>
    </div>
  )
}
