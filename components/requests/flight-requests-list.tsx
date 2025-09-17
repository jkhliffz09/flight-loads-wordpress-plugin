"use client"

import { useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Alert, AlertDescription } from "@/components/ui/alert"
import { MessageCircle, Plane, Clock, AlertCircle } from "lucide-react"
import { format } from "date-fns"
import { FlightResponseForm } from "./flight-response-form"

interface FlightRequest {
  id: number
  flight_number: string
  travel_date: string
  is_return: boolean
  return_flight_number?: string
  return_travel_date?: string
  notes?: string
  status: string
  created_at: string
  user_profile: {
    full_name: string
    username: string
  }
  airline: {
    name: string
    iata_code: string
  }
  from_airport: {
    code: string
    name: string
    city: string
  }
  to_airport: {
    code: string
    name: string
    city: string
  }
  return_from_airport?: {
    code: string
    name: string
    city: string
  }
  return_to_airport?: {
    code: string
    name: string
    city: string
  }
  traveler_airline: {
    name: string
    iata_code: string
  }
}

interface FlightRequestsListProps {
  requests: FlightRequest[]
  currentUser: {
    id: string
    airline_id: number
    airline?: {
      id: number
      name: string
      iata_code: string
    }
  }
}

export function FlightRequestsList({ requests, currentUser }: FlightRequestsListProps) {
  const [expandedRequest, setExpandedRequest] = useState<number | null>(null)
  const [showResponseForm, setShowResponseForm] = useState<number | null>(null)

  const formatRequestTitle = (request: FlightRequest) => {
    const outbound = `${request.airline.iata_code}${request.flight_number} ${format(new Date(request.travel_date), "ddMMMyyyy")} ${request.from_airport.code} to ${request.to_airport.code}`

    if (request.is_return && request.return_flight_number && request.return_travel_date) {
      const returnFlight = `${request.airline.iata_code}${request.return_flight_number} ${format(new Date(request.return_travel_date), "ddMMMyyyy")} ${request.return_from_airport?.code} to ${request.return_to_airport?.code}`
      return `${outbound} / ${returnFlight}`
    }

    return outbound
  }

  const canSeeRequest = (request: FlightRequest) => {
    // User can see their own requests or requests for their airline
    return request.user_profile.username === currentUser.id || request.airline.id === currentUser.airline_id
  }

  const canRespondToRequest = (request: FlightRequest) => {
    // User can respond if they're from the same airline as the requested flight
    return currentUser.airline_id === request.airline.id && request.user_profile.username !== currentUser.id
  }

  const visibleRequests = requests.filter(canSeeRequest)

  // Group requests by airline for better organization
  const requestsByAirline = visibleRequests.reduce(
    (acc, request) => {
      const airlineKey = request.airline.iata_code
      if (!acc[airlineKey]) {
        acc[airlineKey] = {
          airline: request.airline,
          requests: [],
        }
      }
      acc[airlineKey].requests.push(request)
      return acc
    },
    {} as Record<string, { airline: { name: string; iata_code: string }; requests: FlightRequest[] }>,
  )

  if (visibleRequests.length === 0) {
    return (
      <Card>
        <CardContent className="pt-6">
          <div className="text-center space-y-4">
            <Plane className="mx-auto h-12 w-12 text-gray-400" />
            <div>
              <h3 className="text-lg font-medium text-gray-900">No Flight Requests</h3>
              <p className="text-gray-600">
                {currentUser.airline
                  ? `No flight requests available for ${currentUser.airline.name} at this time.`
                  : "No flight requests available at this time."}
              </p>
            </div>
          </div>
        </CardContent>
      </Card>
    )
  }

  return (
    <div className="space-y-6">
      {Object.entries(requestsByAirline).map(([airlineCode, { airline, requests: airlineRequests }]) => (
        <div key={airlineCode}>
          <div className="flex items-center gap-2 mb-4">
            <Plane className="h-5 w-5 text-blue-600" />
            <h2 className="text-xl font-semibold text-gray-900">
              {airline.name} ({airline.iata_code})
            </h2>
            <Badge variant="secondary">{airlineRequests.length} requests</Badge>
          </div>

          <div className="space-y-4">
            {airlineRequests.map((request) => (
              <Card key={request.id} className="hover:shadow-md transition-shadow">
                <CardHeader>
                  <div className="flex items-start justify-between">
                    <div className="flex-1">
                      <CardTitle className="text-lg font-medium text-blue-700">
                        Request: {formatRequestTitle(request)}
                      </CardTitle>
                      <div className="flex items-center gap-4 mt-2 text-sm text-gray-600">
                        <span className="flex items-center gap-1">
                          <Clock className="h-4 w-4" />
                          {format(new Date(request.created_at), "PPp")}
                        </span>
                        <span>by {request.user_profile.full_name}</span>
                        <Badge variant={request.status === "pending" ? "secondary" : "default"}>{request.status}</Badge>
                      </div>
                    </div>

                    <div className="flex items-center gap-2">
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={() => setExpandedRequest(expandedRequest === request.id ? null : request.id)}
                      >
                        {expandedRequest === request.id ? "Hide Details" : "View Details"}
                      </Button>

                      {canRespondToRequest(request) && (
                        <Button
                          size="sm"
                          onClick={() => setShowResponseForm(showResponseForm === request.id ? null : request.id)}
                        >
                          <MessageCircle className="h-4 w-4 mr-1" />
                          Respond
                        </Button>
                      )}
                    </div>
                  </div>
                </CardHeader>

                {expandedRequest === request.id && (
                  <CardContent className="border-t">
                    <div className="space-y-4">
                      <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                          <h4 className="font-medium text-gray-900 mb-2">Flight Details</h4>
                          <div className="space-y-1 text-sm">
                            <p>
                              <strong>Flight:</strong> {request.airline.name} {request.airline.iata_code}
                              {request.flight_number}
                            </p>
                            <p>
                              <strong>Route:</strong> {request.from_airport.name} ({request.from_airport.code}) →{" "}
                              {request.to_airport.name} ({request.to_airport.code})
                            </p>
                            <p>
                              <strong>Date:</strong> {format(new Date(request.travel_date), "PPPP")}
                            </p>
                          </div>
                        </div>

                        <div>
                          <h4 className="font-medium text-gray-900 mb-2">Traveler Info</h4>
                          <div className="space-y-1 text-sm">
                            <p>
                              <strong>Airline Affiliation:</strong> {request.traveler_airline.name} (
                              {request.traveler_airline.iata_code})
                            </p>
                            <p>
                              <strong>Requested by:</strong> {request.user_profile.full_name} (@
                              {request.user_profile.username})
                            </p>
                          </div>
                        </div>
                      </div>

                      {request.is_return && request.return_flight_number && (
                        <div className="border-t pt-4">
                          <h4 className="font-medium text-gray-900 mb-2">Return Flight</h4>
                          <div className="space-y-1 text-sm">
                            <p>
                              <strong>Return Flight:</strong> {request.airline.iata_code}
                              {request.return_flight_number}
                            </p>
                            <p>
                              <strong>Return Route:</strong> {request.return_from_airport?.name} (
                              {request.return_from_airport?.code}) → {request.return_to_airport?.name} (
                              {request.return_to_airport?.code})
                            </p>
                            <p>
                              <strong>Return Date:</strong>{" "}
                              {request.return_travel_date
                                ? format(new Date(request.return_travel_date), "PPPP")
                                : "N/A"}
                            </p>
                          </div>
                        </div>
                      )}

                      {request.notes && (
                        <div className="border-t pt-4">
                          <h4 className="font-medium text-gray-900 mb-2">Notes</h4>
                          <p className="text-sm text-gray-700 bg-gray-50 p-3 rounded">{request.notes}</p>
                        </div>
                      )}
                    </div>
                  </CardContent>
                )}

                {showResponseForm === request.id && canRespondToRequest(request) && (
                  <CardContent className="border-t bg-blue-50">
                    <FlightResponseForm request={request} onClose={() => setShowResponseForm(null)} />
                  </CardContent>
                )}
              </Card>
            ))}
          </div>
        </div>
      ))}

      {/* No airline staff message */}
      {visibleRequests.length > 0 && !visibleRequests.some((req) => canRespondToRequest(req)) && (
        <Alert>
          <AlertCircle className="h-4 w-4" />
          <AlertDescription>
            Sorry. No available members from your requested airlines are currently using the service.
          </AlertDescription>
        </Alert>
      )}
    </div>
  )
}
