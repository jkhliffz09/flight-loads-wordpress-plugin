"use client"

import type React from "react"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { AlertCircle, CheckCircle } from "lucide-react"
import type { ApplicationData } from "./multi-step-form"

interface EmailVerificationStepProps {
  data: ApplicationData
  updateData: (data: Partial<ApplicationData>) => void
  onNext: () => void
  onPrev: () => void
}

export function EmailVerificationStep({ data, updateData, onNext, onPrev }: EmailVerificationStepProps) {
  const [loading, setLoading] = useState(false)
  const [codeSent, setCodeSent] = useState(false)
  const [error, setError] = useState("")
  const [verificationCode, setVerificationCode] = useState("")

  // Get the airline domain from the selected airline
  const getAirlineDomain = async () => {
    if (data.airlineId) {
      try {
        const response = await fetch(`/api/airlines/${data.airlineId}`)
        if (response.ok) {
          const airline = await response.json()
          return airline.domain
        }
      } catch (error) {
        console.error("Error fetching airline domain:", error)
      }
    }
    return "aa.com" // fallback
  }

  const validateEmail = async (email: string) => {
    const airlineDomain = await getAirlineDomain()
    return email.endsWith(`@${airlineDomain}`)
  }

  const sendVerificationCode = async () => {
    if (!data.airlineEmail) {
      setError("Please enter your airline email")
      return
    }

    const isValidDomain = await validateEmail(data.airlineEmail)
    if (!isValidDomain) {
      const domain = await getAirlineDomain()
      setError(`Email must be from ${domain} domain`)
      return
    }

    setLoading(true)
    setError("")

    try {
      const response = await fetch("/api/auth/check-email", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({ email: data.airlineEmail }),
      })

      const result = await response.json()

      if (result.exists) {
        setError("Email already exists in database")
        setLoading(false)
        return
      }

      // In a real implementation, you would send an actual verification code
      // For demo purposes, we'll simulate this
      console.log("Sending verification code to:", data.airlineEmail)

      // Simulate API call delay
      await new Promise((resolve) => setTimeout(resolve, 1000))

      setCodeSent(true)
      setError("")
    } catch (error) {
      console.error("Error sending verification code:", error)
      setError("Failed to send verification code. Please try again.")
    } finally {
      setLoading(false)
    }
  }

  const verifyCode = async () => {
    if (!verificationCode) {
      setError("Please enter the verification code")
      return
    }

    setLoading(true)
    setError("")

    try {
      // In a real implementation, you would verify the code with your backend
      // For demo purposes, we'll accept "123456" as the valid code
      if (verificationCode === "123456") {
        updateData({
          isEmailVerified: true,
          verificationCode: verificationCode,
        })
        setError("")
      } else {
        setError("Invalid verification code. Please try again.")
        setLoading(false)
        return
      }
    } catch (error) {
      console.error("Error verifying code:", error)
      setError("Failed to verify code. Please try again.")
    } finally {
      setLoading(false)
    }
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()

    if (!data.isEmailVerified) {
      setError("Please verify your email first")
      return
    }

    onNext()
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <Label htmlFor="airlineEmail">Airline Affiliated Email *</Label>
        <Input
          id="airlineEmail"
          type="email"
          value={data.airlineEmail || ""}
          onChange={(e) => updateData({ airlineEmail: e.target.value })}
          placeholder="your.name@airline.com"
          disabled={data.isEmailVerified}
          required
        />
        <p className="text-sm text-gray-500 mt-1">Must be from your selected airline's domain</p>
      </div>

      {error && (
        <Alert variant="destructive">
          <AlertCircle className="h-4 w-4" />
          <AlertDescription>{error}</AlertDescription>
        </Alert>
      )}

      {data.isEmailVerified && (
        <Alert>
          <CheckCircle className="h-4 w-4" />
          <AlertDescription>Email verified successfully!</AlertDescription>
        </Alert>
      )}

      {!data.isEmailVerified && (
        <>
          <Button
            type="button"
            onClick={sendVerificationCode}
            disabled={loading || !data.airlineEmail}
            className="w-full"
          >
            {loading ? "Sending..." : codeSent ? "Resend Code" : "Send Verification Code"}
          </Button>

          {codeSent && (
            <div className="space-y-4">
              <div>
                <Label htmlFor="verificationCode">Verification Code *</Label>
                <Input
                  id="verificationCode"
                  value={verificationCode}
                  onChange={(e) => setVerificationCode(e.target.value)}
                  placeholder="Enter 6-digit code"
                  maxLength={6}
                  required
                />
                <p className="text-sm text-gray-500 mt-1">Enter the code sent to your email (use "123456" for demo)</p>
              </div>

              <Button type="button" onClick={verifyCode} disabled={loading || !verificationCode} className="w-full">
                {loading ? "Verifying..." : "Verify Code"}
              </Button>
            </div>
          )}
        </>
      )}

      <div className="flex gap-4">
        <Button type="button" variant="outline" onClick={onPrev} className="flex-1 bg-transparent">
          Back
        </Button>
        <Button type="submit" disabled={!data.isEmailVerified} className="flex-1">
          Continue
        </Button>
      </div>
    </form>
  )
}
