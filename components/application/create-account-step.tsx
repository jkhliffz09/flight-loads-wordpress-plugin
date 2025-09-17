"use client"

import type React from "react"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { CheckCircle, AlertCircle } from "lucide-react"
import { useRouter } from "next/navigation"
import type { ApplicationData } from "./multi-step-form"

interface CreateAccountStepProps {
  data: ApplicationData
  updateData: (data: Partial<ApplicationData>) => void
  onPrev: () => void
}

export function CreateAccountStep({ data, updateData, onPrev }: CreateAccountStepProps) {
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)
  const [error, setError] = useState("")
  const router = useRouter()

  const validateForm = () => {
    if (!data.username || !data.email || !data.password || !data.confirmPassword) {
      setError("All fields are required")
      return false
    }

    if (data.password !== data.confirmPassword) {
      setError("Passwords do not match")
      return false
    }

    if (data.password.length < 6) {
      setError("Password must be at least 6 characters long")
      return false
    }

    return true
  }

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!validateForm()) return

    setLoading(true)
    setError("")

    try {
      const formData = new FormData()

      // Add all form data
      formData.append("fullName", data.fullName)
      formData.append("username", data.username)
      formData.append("email", data.email)
      formData.append("password", data.password)
      formData.append("airlineId", data.airlineId?.toString() || "")
      formData.append("status", data.status)

      if (data.status === "retired") {
        formData.append("phoneNumber", data.phoneNumber || "")
        formData.append("retirementDate", data.retirementDate || "")
        formData.append("exAirlineJob", data.exAirlineJob || "")
        formData.append("yearsWorked", data.yearsWorked?.toString() || "")
        if (data.retiredIdFile) {
          formData.append("retiredIdFile", data.retiredIdFile)
        }
      }

      const response = await fetch("/api/auth/register", {
        method: "POST",
        body: formData,
      })

      const result = await response.json()

      if (!response.ok) {
        throw new Error(result.error || "Failed to create account")
      }

      setSuccess(true)

      // Redirect after a delay
      setTimeout(() => {
        router.push("/application-submitted")
      }, 3000)
    } catch (error: any) {
      console.error("Error creating account:", error)
      setError(error.message || "Failed to create account. Please try again.")
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="text-center space-y-4">
        <CheckCircle className="mx-auto h-16 w-16 text-green-500" />
        <h2 className="text-2xl font-bold text-green-700">Application Submitted!</h2>
        <div className="space-y-2">
          <p className="text-gray-600">Your application has been submitted successfully and is pending approval.</p>
          <p className="text-gray-600">You will receive an email notification once your account is approved.</p>
          <p className="text-sm text-gray-500">Redirecting you to confirmation page...</p>
        </div>
      </div>
    )
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <Label htmlFor="username">Username *</Label>
        <Input
          id="username"
          value={data.username}
          onChange={(e) => updateData({ username: e.target.value })}
          placeholder="Choose a unique username"
          required
        />
      </div>

      <div>
        <Label htmlFor="email">Email *</Label>
        <Input
          id="email"
          type="email"
          value={data.email}
          onChange={(e) => updateData({ email: e.target.value })}
          placeholder="your.email@example.com"
          required
        />
        <p className="text-sm text-gray-500 mt-1">
          This will be your login email (can be different from airline email)
        </p>
      </div>

      <div>
        <Label htmlFor="password">Password *</Label>
        <Input
          id="password"
          type="password"
          value={data.password}
          onChange={(e) => updateData({ password: e.target.value })}
          placeholder="Choose a secure password"
          required
        />
      </div>

      <div>
        <Label htmlFor="confirmPassword">Confirm Password *</Label>
        <Input
          id="confirmPassword"
          type="password"
          value={data.confirmPassword}
          onChange={(e) => updateData({ confirmPassword: e.target.value })}
          placeholder="Confirm your password"
          required
        />
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertCircle className="h-4 w-4" />
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      <div className="flex gap-4">
        <Button type="button" variant="outline" onClick={onPrev} className="flex-1 bg-transparent">
          Back
        </Button>
        <Button type="submit" disabled={loading} className="flex-1">
          {loading ? "Submitting..." : "Submit Application"}
        </Button>
      </div>
    </form>
  )
}
