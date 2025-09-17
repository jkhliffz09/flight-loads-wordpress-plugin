"use client"

import { useState, useEffect } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { Textarea } from "@/components/ui/textarea"
import { Checkbox } from "@/components/ui/checkbox"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { CalendarIcon, Plane, CheckCircle } from "lucide-react"
import { format, addDays, subDays } from "date-fns"
import { cn } from "@/lib/utils"
import { useRouter } from "next/navigation"

interface FlightRequestFormProps {
  userProfile: {
    id: string
    airline_id: number | null
    airline_name?: string
    iata_code?: string
  }
}

interface Airline {
  id: number
  name: string
  iata_code: string
}

interface Airport {
  id: number
  code: string
  name: string
  city: string
}

export function FlightRequestForm({ userProfile }: FlightRequestFormProps) {
  const [step, setStep] = useState<"form" | "review" | "success">("form")
  const [loading, setLoading] = useState(false)
  const [error, setError] = useState("")
  const router = useRouter()

  // Form data
  const [formData, setFormData] = useState({
    airlineId: null as number | null,
    flightNumber: "",
    fromAirportId: null as number | null,
    toAirportId: null as number | null,
    travelDate: null as Date | null,
    isReturn: false,
    returnFlightNumber: "",
    returnFromAirportId: null as number | null,
    returnToAirportId: null as number | null,
    returnTravelDate: null as Date | null,
    travelerAirlineId: userProfile.airline_id,
    notes: "",
  })

  // Search states
  const [airlineSearch, setAirlineSearch] = useState("")
  const [airlines, setAirlines] = useState<Airline[]>([])
  const [showAirlines, setShowAirlines] = useState(false)

  const [fromSearch, setFromSearch] = useState("")
  const [fromAirports, setFromAirports] = useState<Airport[]>([])
  const [showFromAirports, setShowFromAirports] = useState(false)

  const [toSearch, setToSearch] = useState("")
  const [toAirports, setToAirports] = useState<Airport[]>([])
  const [showToAirports, setShowToAirports] = useState(false)

  const [travelerAirlineSearch, setTravelerAirlineSearch] = useState(userProfile.airline_name || "")
  const [travelerAirlines, setTravelerAirlines] = useState<Airline[]>([])
  const [showTravelerAirlines, setShowTravelerAirlines] = useState(false)

  // Selected items for display
  const [selectedAirline, setSelectedAirline] = useState<Airline | null>(null)
  const [selectedFromAirport, setSelectedFromAirport] = useState<Airport | null>(null)
  const [selectedToAirport, setSelectedToAirport] = useState<Airport | null>(null)
  const [selectedTravelerAirline, setSelectedTravelerAirline] = useState<Airline | null>(
    userProfile.airline_name
      ? {
          id: userProfile.airline_id!,
          name: userProfile.airline_name,
          iata_code: userProfile.iata_code || "",
        }
      : null,
  )

  // Auto-fill return flight when return is checked
  useEffect(() => {
    if (formData.isReturn && selectedFromAirport && selectedToAirport) {
      setFormData((prev) => ({
        ...prev,
        returnFromAirportId: prev.toAirportId, // Switch: return from = original to
        returnToAirportId: prev.fromAirportId, // Switch: return to = original from
        returnFlightNumber: prev.flightNumber, // Same flight number initially
      }))
    }
  }, [formData.isReturn, selectedFromAirport, selectedToAirport])

  const handleAirlineSearch = async (searchTerm: string) => {
    setAirlineSearch(searchTerm)
    if (searchTerm.length >= 2) {
      try {
        const response = await fetch(`/api/airlines/search?q=${encodeURIComponent(searchTerm)}`)
        if (response.ok) {
          const results = await response.json()
          setAirlines(results)
          setShowAirlines(true)
        }
      } catch (error) {
        console.error("Error searching airlines:", error)
      }
    } else {
      setShowAirlines(false)
    }
  }

  const handleFromAirportSearch = async (searchTerm: string) => {
    setFromSearch(searchTerm)
    if (searchTerm.length >= 2) {
      try {
        const response = await fetch(`/api/airports/search?q=${encodeURIComponent(searchTerm)}`)
        if (response.ok) {
          const results = await response.json()
          setFromAirports(results)
          setShowFromAirports(true)
        }
      } catch (error) {
        console.error("Error searching airports:", error)
      }
    } else {
      setShowFromAirports(false)
    }
  }

  const handleToAirportSearch = async (searchTerm: string) => {
    setToSearch(searchTerm)
    if (searchTerm.length >= 2) {
      try {
        const response = await fetch(`/api/airports/search?q=${encodeURIComponent(searchTerm)}`)
        if (response.ok) {
          const results = await response.json()
          setToAirports(results)
          setShowToAirports(true)
        }
      } catch (error) {
        console.error("Error searching airports:", error)
      }
    } else {
      setShowToAirports(false)
    }
  }

  const handleTravelerAirlineSearch = async (searchTerm: string) => {
    setTravelerAirlineSearch(searchTerm)
    if (searchTerm.length >= 2) {
      try {
        const response = await fetch(`/api/airlines/search?q=${encodeURIComponent(searchTerm)}`)
        if (response.ok) {
          const results = await response.json()
          setTravelerAirlines(results)
          setShowTravelerAirlines(true)
        }
      } catch (error) {
        console.error("Error searching airlines:", error)
      }
    } else {
      setShowTravelerAirlines(false)
    }
  }

  const selectAirline = (airline: Airline) => {
    setSelectedAirline(airline)
    setAirlineSearch(airline.name)
    setFormData((prev) => ({ ...prev, airlineId: airline.id }))
    setShowAirlines(false)
  }

  const selectFromAirport = (airport: Airport) => {
    setSelectedFromAirport(airport)
    setFromSearch(`${airport.code} - ${airport.name}`)
    setFormData((prev) => ({ ...prev, fromAirportId: airport.id }))
    setShowFromAirports(false)
  }

  const selectToAirport = (airport: Airport) => {
    setSelectedToAirport(airport)
    setToSearch(`${airport.code} - ${airport.name}`)
    setFormData((prev) => ({ ...prev, toAirportId: airport.id }))
    setShowToAirports(false)
  }

  const selectTravelerAirline = (airline: Airline) => {
    setSelectedTravelerAirline(airline)
    setTravelerAirlineSearch(airline.name)
    setFormData((prev) => ({ ...prev, travelerAirlineId: airline.id }))
    setShowTravelerAirlines(false)
  }

  const formatFlightNumber = (value: string) => {
    // Remove any non-alphanumeric characters and limit to 4 characters
    const cleaned = value.replace(/[^a-zA-Z0-9]/g, "").slice(0, 4)
    // Convert leading zeros: if it starts with 0, remove leading zeros
    return cleaned.replace(/^0+/, "") || (cleaned.startsWith("0") ? "0" : cleaned)
  }

  const validateForm = () => {
    if (
      !formData.airlineId ||
      !formData.flightNumber ||
      !formData.fromAirportId ||
      !formData.toAirportId ||
      !formData.travelDate ||
      !formData.travelerAirlineId
    ) {
      setError("Please fill in all required fields")
      return false
    }

    if (formData.isReturn && (!formData.returnFlightNumber || !formData.returnTravelDate)) {
      setError("Please fill in return flight information")
      return false
    }

    return true
  }

  const handleSubmit = async () => {
    if (!validateForm()) return

    setLoading(true)
    setError("")

    try {
      const requestData = {
        airlineId: formData.airlineId,
        flightNumber: formData.flightNumber,
        fromAirportId: formData.fromAirportId,
        toAirportId: formData.toAirportId,
        travelDate: formData.travelDate?.toISOString().split("T")[0],
        isReturn: formData.isReturn,
        returnFlightNumber: formData.isReturn ? formData.returnFlightNumber : null,
        returnFromAirportId: formData.isReturn ? formData.returnFromAirportId : null,
        returnToAirportId: formData.isReturn ? formData.returnToAirportId : null,
        returnTravelDate: formData.isReturn ? formData.returnTravelDate?.toISOString().split("T")[0] : null,
        travelerAirlineId: formData.travelerAirlineId,
        notes: formData.notes || null,
      }

      const response = await fetch("/api/flight-requests", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(requestData),
      })

      const result = await response.json()

      if (!response.ok) {
        throw new Error(result.error || "Failed to submit request")
      }

      setStep("success")
    } catch (error: any) {
      console.error("Error submitting request:", error)
      setError(error.message || "Failed to submit request. Please try again.")
    } finally {
      setLoading(false)
    }
  }

  const resetForm = () => {
    setStep("form")
    setFormData({
      airlineId: null,
      flightNumber: "",
      fromAirportId: null,
      toAirportId: null,
      travelDate: null,
      isReturn: false,
      returnFlightNumber: "",
      returnFromAirportId: null,
      returnToAirportId: null,
      returnTravelDate: null,
      travelerAirlineId: userProfile.airline_id,
      notes: "",
    })
    setAirlineSearch("")
    setFromSearch("")
    setToSearch("")
    setTravelerAirlineSearch(userProfile.airline_name || "")
    setSelectedAirline(null)
    setSelectedFromAirport(null)
    setSelectedToAirport(null)
    setSelectedTravelerAirline(
      userProfile.airline_name
        ? {
            id: userProfile.airline_id!,
            name: userProfile.airline_name,
            iata_code: userProfile.iata_code || "",
          }
        : null,
    )
    setError("")
  }

  if (step === "success") {
    return (
      <Card>
        <CardContent className="pt-6">
          <div className="text-center space-y-4">
            <CheckCircle className="mx-auto h-16 w-16 text-green-500" />
            <h2 className="text-2xl font-bold text-green-700">Request Submitted!</h2>
            <p className="text-gray-600">Your flight load request has been submitted successfully.</p>
            <p className="text-gray-600">Airline staff will be able to see and respond to your request.</p>
            <div className="flex gap-4 justify-center mt-6">
              <Button onClick={resetForm}>Submit Another Request</Button>
              <Button variant="outline" onClick={() => router.refresh()}>
                View All Requests
              </Button>
            </div>
          </div>
        </CardContent>
      </Card>
    )
  }

  if (step === "review") {
    return (
      <Card>
        <CardHeader>
          <CardTitle>Review Your Request</CardTitle>
        </CardHeader>
        <CardContent className="space-y-4">
          <div className="bg-gray-50 p-4 rounded-lg space-y-2">
            <p>
              <strong>Flight:</strong> {selectedAirline?.name} ({selectedAirline?.iata_code}
              {formData.flightNumber})
            </p>
            <p>
              <strong>Route:</strong> {selectedFromAirport?.code} → {selectedToAirport?.code}
            </p>
            <p>
              <strong>Date:</strong> {formData.travelDate ? format(formData.travelDate, "PPP") : ""}
            </p>
            {formData.isReturn && (
              <>
                <p>
                  <strong>Return Flight:</strong> {selectedAirline?.iata_code}
                  {formData.returnFlightNumber}
                </p>
                <p>
                  <strong>Return Date:</strong>{" "}
                  {formData.returnTravelDate ? format(formData.returnTravelDate, "PPP") : ""}
                </p>
              </>
            )}
            <p>
              <strong>Traveler Airline:</strong> {selectedTravelerAirline?.name}
            </p>
            {formData.notes && (
              <p>
                <strong>Notes:</strong> {formData.notes}
              </p>
            )}
          </div>

          {error && (
            <Alert variant="destructive">
              <AlertDescription>{error}</AlertDescription>
            </Alert>
          )}

          <div className="flex gap-4">
            <Button variant="outline" onClick={() => setStep("form")} className="flex-1 bg-transparent">
              Edit Request
            </Button>
            <Button onClick={handleSubmit} disabled={loading} className="flex-1">
              {loading ? "Submitting..." : "Submit Request"}
            </Button>
          </div>
        </CardContent>
      </Card>
    )
  }

  const today = new Date()
  const yesterday = subDays(today, 1)
  const tomorrow = addDays(today, 1)

  return (
    <Card>
      <CardHeader>
        <CardTitle className="flex items-center gap-2">
          <Plane className="h-5 w-5" />
          Request Flight Loads
        </CardTitle>
      </CardHeader>
      <CardContent className="space-y-6">
        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* Airline Name */}
          <div className="relative">
            <Label htmlFor="airline">Airline Name *</Label>
            <Input
              id="airline"
              value={airlineSearch}
              onChange={(e) => handleAirlineSearch(e.target.value)}
              placeholder="Start typing airline name..."
              required
            />
            {showAirlines && airlines.length > 0 && (
              <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                {airlines.map((airline) => (
                  <button
                    key={airline.id}
                    type="button"
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100"
                    onClick={() => selectAirline(airline)}
                  >
                    <div className="font-medium">{airline.name}</div>
                    <div className="text-sm text-gray-500">{airline.iata_code}</div>
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* Flight Number */}
          <div>
            <Label htmlFor="flightNumber">Flight Number *</Label>
            <Input
              id="flightNumber"
              value={formData.flightNumber}
              onChange={(e) => setFormData((prev) => ({ ...prev, flightNumber: formatFlightNumber(e.target.value) }))}
              placeholder="e.g., 1234"
              maxLength={4}
              required
            />
            <p className="text-xs text-gray-500 mt-1">4 characters max, leading zeros will be removed</p>
          </div>
        </div>

        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
          {/* From Airport */}
          <div className="relative">
            <Label htmlFor="from">From *</Label>
            <Input
              id="from"
              value={fromSearch}
              onChange={(e) => handleFromAirportSearch(e.target.value)}
              placeholder="Start typing airport name or code..."
              required
            />
            {showFromAirports && fromAirports.length > 0 && (
              <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                {fromAirports.map((airport) => (
                  <button
                    key={airport.id}
                    type="button"
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100"
                    onClick={() => selectFromAirport(airport)}
                  >
                    <div className="font-medium">
                      {airport.code} - {airport.name}
                    </div>
                    <div className="text-sm text-gray-500">{airport.city}</div>
                  </button>
                ))}
              </div>
            )}
          </div>

          {/* To Airport */}
          <div className="relative">
            <Label htmlFor="to">To *</Label>
            <Input
              id="to"
              value={toSearch}
              onChange={(e) => handleToAirportSearch(e.target.value)}
              placeholder="Start typing airport name or code..."
              required
            />
            {showToAirports && toAirports.length > 0 && (
              <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
                {toAirports.map((airport) => (
                  <button
                    key={airport.id}
                    type="button"
                    className="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100"
                    onClick={() => selectToAirport(airport)}
                  >
                    <div className="font-medium">
                      {airport.code} - {airport.name}
                    </div>
                    <div className="text-sm text-gray-500">{airport.city}</div>
                  </button>
                ))}
              </div>
            )}
          </div>
        </div>

        {/* Travel Date */}
        <div>
          <Label>Date of Travel *</Label>
          <Popover>
            <PopoverTrigger asChild>
              <Button
                variant="outline"
                className={cn(
                  "w-full justify-start text-left font-normal",
                  !formData.travelDate && "text-muted-foreground",
                )}
              >
                <CalendarIcon className="mr-2 h-4 w-4" />
                {formData.travelDate ? format(formData.travelDate, "PPP") : "Pick a date"}
              </Button>
            </PopoverTrigger>
            <PopoverContent className="w-auto p-0">
              <Calendar
                mode="single"
                selected={formData.travelDate || undefined}
                onSelect={(date) => setFormData((prev) => ({ ...prev, travelDate: date || null }))}
                disabled={(date) => date < yesterday || date > tomorrow}
                initialFocus
              />
            </PopoverContent>
          </Popover>
          <p className="text-xs text-gray-500 mt-1">Yesterday, today, and tomorrow only</p>
        </div>

        {/* Return Flight */}
        <div className="space-y-4">
          <div className="flex items-center space-x-2">
            <Checkbox
              id="return"
              checked={formData.isReturn}
              onCheckedChange={(checked) => setFormData((prev) => ({ ...prev, isReturn: !!checked }))}
            />
            <Label htmlFor="return">Return Flight</Label>
          </div>

          {formData.isReturn && (
            <div className="space-y-4 p-4 bg-blue-50 rounded-lg border border-blue-200">
              <h3 className="font-medium text-blue-800">Return Flight Information</h3>

              <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                  <Label htmlFor="returnFlightNumber">Return Flight Number *</Label>
                  <Input
                    id="returnFlightNumber"
                    value={formData.returnFlightNumber}
                    onChange={(e) =>
                      setFormData((prev) => ({ ...prev, returnFlightNumber: formatFlightNumber(e.target.value) }))
                    }
                    placeholder="e.g., 1235"
                    maxLength={4}
                    required
                  />
                </div>

                <div>
                  <Label>Return Date *</Label>
                  <Popover>
                    <PopoverTrigger asChild>
                      <Button
                        variant="outline"
                        className={cn(
                          "w-full justify-start text-left font-normal",
                          !formData.returnTravelDate && "text-muted-foreground",
                        )}
                      >
                        <CalendarIcon className="mr-2 h-4 w-4" />
                        {formData.returnTravelDate ? format(formData.returnTravelDate, "PPP") : "Pick a date"}
                      </Button>
                    </PopoverTrigger>
                    <PopoverContent className="w-auto p-0">
                      <Calendar
                        mode="single"
                        selected={formData.returnTravelDate || undefined}
                        onSelect={(date) => setFormData((prev) => ({ ...prev, returnTravelDate: date || null }))}
                        disabled={(date) => date < yesterday || date > tomorrow}
                        initialFocus
                      />
                    </PopoverContent>
                  </Popover>
                </div>
              </div>

              <p className="text-sm text-blue-700">
                Return route: {selectedToAirport?.code} → {selectedFromAirport?.code}
              </p>
            </div>
          )}
        </div>

        {/* Traveler Airline Affiliation */}
        <div className="relative">
          <Label htmlFor="travelerAirline">Airline Affiliation of Person Traveling *</Label>
          <Input
            id="travelerAirline"
            value={travelerAirlineSearch}
            onChange={(e) => handleTravelerAirlineSearch(e.target.value)}
            placeholder="Start typing airline name..."
            required
          />
          {showTravelerAirlines && travelerAirlines.length > 0 && (
            <div className="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-auto">
              {travelerAirlines.map((airline) => (
                <button
                  key={airline.id}
                  type="button"
                  className="w-full px-4 py-2 text-left hover:bg-gray-100 focus:bg-gray-100"
                  onClick={() => selectTravelerAirline(airline)}
                >
                  <div className="font-medium">{airline.name}</div>
                  <div className="text-sm text-gray-500">{airline.iata_code}</div>
                </button>
              ))}
            </div>
          )}
        </div>

        {/* Notes */}
        <div>
          <Label htmlFor="notes">Notes</Label>
          <Textarea
            id="notes"
            value={formData.notes}
            onChange={(e) => setFormData((prev) => ({ ...prev, notes: e.target.value }))}
            placeholder="Additional information or special requests..."
            maxLength={300}
            rows={3}
          />
          <p className="text-xs text-gray-500 mt-1">{formData.notes.length}/300 characters</p>
        </div>

        {error && (
          <Alert variant="destructive">
            <AlertDescription>{error}</AlertDescription>
          </Alert>
        )}

        <Button onClick={() => setStep("review")} className="w-full" disabled={!validateForm()}>
          Review Request
        </Button>
      </CardContent>
    </Card>
  )
}
