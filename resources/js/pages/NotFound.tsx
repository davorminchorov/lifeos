import React from "react"
import { Link } from "react-router-dom"
import { Button } from "../components/ui/Button"

export default function NotFound() {
  return (
    <div className="container flex flex-col items-center justify-center min-h-screen py-12">
      <div className="w-full max-w-md text-center">
        {/* Logo */}
        <div className="mb-8 flex justify-center">
          <div className="w-12 h-12 rounded-full bg-teal-600 flex items-center justify-center text-white text-lg font-bold">
            L
          </div>
        </div>

        <div className="bg-white shadow-md rounded-lg p-8">
          <h1 className="text-6xl font-bold text-slate-800 mb-2">404</h1>
          <h2 className="text-2xl font-semibold text-slate-700 mb-4">Page Not Found</h2>
          <p className="text-slate-600 mb-6">
            The page you are looking for doesn't exist or has been moved.
          </p>

          <Link to="/">
            <Button fullWidth>Go Back Home</Button>
          </Link>
        </div>
      </div>
    </div>
  )
}
