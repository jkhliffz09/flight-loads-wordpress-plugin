"use client"

import type React from "react"

import { useState } from "react"
import { Button } from "@/components/ui/button"
import { Textarea } from "@/components/ui/textarea"
import { Label } from "@/components/ui/label"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { CheckCircle, AlertCircle } from "lucide-react"
import { supabase } from "@/lib/supabase/client"

interface FlightResponseFormProps {
  request: {
    id: number
    flight_number: string
    airline: {
      name: string
      iata_code: string
    }
    from_airport: {
      code: string
      name: string
    }
    to_airport: {
      code: string
      name: string
    }
    travel_date: string
  }
  onClose: () => void
}

export function FlightResponseForm({ request, onClose }: FlightResponseFormProps) {
  const [responseText, setResponseText] = useState("")
  const [loading, setLoading] = useState(false)
  const [success, setSuccess] = useState(false)
  const [error, setError] = useState("")

  const handleSubmit = async (e: React.FormEvent) => {
    e.preventDefault()

    if (!responseText.trim()) {
      setError("Please provide flight load information")
      return
    }

    setLoading(true)
    setError("")

    try {
      const { data: user } = await supabase.auth.getUser()
      if (!user.user) throw new Error("Not authenticated")

      const { error: insertError } = await supabase.from("flight_responses").insert([
        {
          request_id: request.id,
          responder_id: user.user.id,
          response_text: responseText.trim(),
        },
      ])

      if (insertError) throw insertError

      // Update request status to answered
      const { error: updateError } = await supabase
        .from("flight_requests")
        .update({ status: "answered" })
        .eq("id", request.id)

      if (updateError) throw updateError

      setSuccess(true)
      setTimeout(() => {
        onClose()
      }, 2000)
    } catch (error: any) {
      console.error("Error submitting response:", error)
      setError(error.message || "Failed to submit response. Please try again.")
    } finally {
      setLoading(false)
    }
  }

  if (success) {
    return (
      <div className="text-center space-y-4">
        <CheckCircle className="mx-auto h-12 w-12 text-green-500" />
        <div>
          <h3 className="text-lg font-medium text-green-700">Response Submitted!</h3>
          <p className="text-sm text-gray-600">Your flight load information has been provided.</p>
        </div>
      </div>
    )
  }

  return (
    <div className="space-y-4">
      <div>
        <h3 className="text-lg font-medium text-gray-900 mb-2">Give Flight Loads</h3>
        <div className="bg-white p-3 rounded border text-sm">
          <p className="font-medium">
            Request: {request.airline.iata_code}
            {request.flight_number}{" "}
            {new Date(request.travel_date)
              .toLocaleDateString("en-GB", {
                day: "2-digit",
                month: "short",
                year: "numeric",
              })
              .replace(/ /g, "")}{" "}
            {request.from_airport.code} to {request.to_airport.code}
          </p>
        </div>
      </div>

      <form onSubmit={handleSubmit} className="space-y-4">
        <div>
          <Label htmlFor="response">Flight Load Information *</Label>
          <Textarea
            id="response"
            value={responseText}
            onChange={(e) => setResponseText(e.target.value)}
            placeholder="Provide flight load information, availability, or any relevant details..."
            rows={4}
            required
          />
          <p className="text-xs text-gray-500 mt-1">
            Share load factors, seat availability, or other helpful information
          </p>
        </div>

        {error && (
          <Alert variant="destructive">
            <AlertCircle className="h-4 w-4" />
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        <div className="flex gap-2">
          <Button type="button" variant="outline" onClick={onClose} className="flex-1 bg-transparent">
            Cancel
          </Button>
          <Button type="submit" disabled={loading || !responseText.trim()} className="flex-1">
            {loading ? "Submitting..." : "Submit Response"}
          </Button>
        </div>
      </form>
    </div>
  )
}
