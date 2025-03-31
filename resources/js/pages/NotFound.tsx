import React from 'react'
import { Link } from 'react-router-dom'
import { Button } from '../components/ui/button'

export default function NotFound() {
  return (
    <div className="min-h-screen bg-[#F8FAFC] flex flex-col justify-center items-center p-4 text-center">
      <div className="w-full max-w-md">
        <div className="mb-4 flex justify-center">
          {/* Logo */}
          <div className="w-12 h-12 rounded-full bg-[#0F766E] flex items-center justify-center text-white text-lg font-bold">
            L
          </div>
        </div>

        <h1 className="text-6xl font-bold text-[#0F766E] mb-4">404</h1>
        <h2 className="text-2xl font-semibold text-[#1E293B] mb-4">Page Not Found</h2>
        <p className="text-[#64748B] mb-8">
          The page you are looking for doesn't exist or has been moved.
        </p>

        <Link to="/">
          <Button variant="primary" className="mx-auto">
            Back to Home
          </Button>
        </Link>
      </div>
    </div>
  )
}
