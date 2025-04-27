import React from "react"
import { Link } from "react-router-dom"
import { Button } from "../ui"

export default function NotFound() {
  return (
    <div className="container flex flex-col items-center justify-center min-h-screen py-12 bg-background">
      <div className="w-full max-w-md text-center">
        {/* Logo */}
        <div className="mb-8 flex justify-center">
          <div className="w-12 h-12 rounded-full bg-primary flex items-center justify-center text-on-primary text-lg font-bold shadow-elevation-2">
            L
          </div>
        </div>

        <div className="bg-surface shadow-elevation-2 rounded-lg p-8">
          <h1 className="text-6xl font-bold text-on-surface mb-2">404</h1>
          <h2 className="text-2xl font-semibold text-on-surface mb-4">Page Not Found</h2>
          <p className="text-on-surface-variant mb-6">
            The page you are looking for doesn't exist or has been moved.
          </p>

          <Link to="/">
            <Button variant="filled" fullWidth>Go Back Home</Button>
          </Link>
        </div>
      </div>
    </div>
  )
}
