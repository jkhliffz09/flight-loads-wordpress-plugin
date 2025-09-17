"use client"

import type React from "react"

import { useState, useEffect } from "react"
import { Button } from "@/components/ui/button"
import { Input } from "@/components/ui/input"
import { Label } from "@/components/ui/label"
import { RadioGroup, RadioGroupItem } from "@/components/ui/radio-group"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { CalendarIcon, Upload } from "lucide-react"
import { format } from "date-fns"
import { cn } from "@/lib/utils"
import type { ApplicationData } from "./multi-step-form"

interface BasicInformationStepProps {
  data: ApplicationData
  updateData: (data: Partial<ApplicationData>) => void
  onNext: () => void
}

interface Airline {
  id: number
  name: string
  iata_code: string
  domain: string
}

export function BasicInformationStep({ data, updateData, onNext }: BasicInformationStepProps) {
  const [airlines, setAirlines] = useState<Airline[]>([])
  const [airlineSearch, setAirlineSearch] = useState(data.airlineName)
  const [showAirlines, setShowAirlines] = useState(false)
  const [retirementDate, setRetirementDate] = useState<Date>()
  const [loading, setLoading] = useState(false)

  useEffect(() => {
    if (data.retirementDate) {
      setRetirementDate(new Date(data.retirementDate))
    }
  }, [data.retirementDate])

  const handleAirlineSearch = async (searchTerm: string) => {
    setAirlineSearch(searchTerm)
    updateData({ airlineName: searchTerm })

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

  const selectAirline = (airline: Airline) => {
    setAirlineSearch(airline.name)
    updateData({
      airlineName: airline.name,
      airlineId: airline.id,
    })
    setShowAirlines(false)
  }

  const handleFileUpload = (e: React.ChangeEvent<HTMLInputElement>) => {
    const file = e.target.files?.[0]
    if (file) {
      updateData({ retiredIdFile: file })
    }
  }

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault()

    if (!data.fullName || !data.airlineName) {
      alert("Please fill in all required fields")
      return
    }

    if (data.status === "retired") {
      if (!data.phoneNumber || !data.retirementDate || !data.exAirlineJob || !data.yearsWorked) {
        alert("Please fill in all retirement information")
        return
      }
    }

    onNext()
  }

  return (
    <form onSubmit={handleSubmit} className="space-y-6">
      <div>
        <Label htmlFor="fullName">Full Name *</Label>
        <Input
          id="fullName"
          value={data.fullName}
          onChange={(e) => updateData({ fullName: e.target.value })}
          required
        />
      </div>

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
                <div className="text-sm text-gray-500">
                  {airline.iata_code} • {airline.domain}
                </div>
              </button>
            ))}
          </div>
        )}
      </div>

      <div>
        <Label>Status *</Label>
        <RadioGroup
          value={data.status}
          onValueChange={(value: "active" | "retired") => updateData({ status: value })}
          className="mt-2"
        >
          <div className="flex items-center space-x-2">
            <RadioGroupItem value="active" id="active" />
            <Label htmlFor="active">Active</Label>
          </div>
          <div className="flex items-center space-x-2">
            <RadioGroupItem value="retired" id="retired" />
            <Label htmlFor="retired">Retired</Label>
          </div>
        </RadioGroup>
      </div>

      {data.status === "retired" && (
        <div className="space-y-4 p-4 bg-yellow-50 rounded-lg border border-yellow-200">
          <h3 className="font-medium text-yellow-800">Retirement Information</h3>

          <div>
            <Label htmlFor="phoneNumber">Phone Number *</Label>
            <Input
              id="phoneNumber"
              value={data.phoneNumber || ""}
              onChange={(e) => updateData({ phoneNumber: e.target.value })}
              required
            />
          </div>

          <div>
            <Label>Retirement Date *</Label>
            <Popover>
              <PopoverTrigger asChild>
                <Button
                  variant="outline"
                  className={cn(
                    "w-full justify-start text-left font-normal",
                    !retirementDate && "text-muted-foreground",
                  )}
                >
                  <CalendarIcon className="mr-2 h-4 w-4" />
                  {retirementDate ? format(retirementDate, "PPP") : "Pick a date"}
                </Button>
              </PopoverTrigger>
              <PopoverContent className="w-auto p-0">
                <Calendar
                  mode="single"
                  selected={retirementDate}
                  onSelect={(date) => {
                    setRetirementDate(date)
                    updateData({ retirementDate: date?.toISOString() })
                  }}
                  initialFocus
                />
              </PopoverContent>
            </Popover>
          </div>

          <div>
            <Label htmlFor="exAirlineJob">Ex-Airline Job *</Label>
            <Input
              id="exAirlineJob"
              value={data.exAirlineJob || ""}
              onChange={(e) => updateData({ exAirlineJob: e.target.value })}
              placeholder="e.g., Flight Attendant, Pilot, Ground Crew"
              required
            />
          </div>

          <div>
            <Label htmlFor="yearsWorked">Years Worked *</Label>
            <Input
              id="yearsWorked"
              type="number"
              min="1"
              value={data.yearsWorked || ""}
              onChange={(e) => updateData({ yearsWorked: Number.parseInt(e.target.value) })}
              required
            />
          </div>

          <div>
            <Label htmlFor="retiredId">Upload Retired ID *</Label>
            <div className="mt-1">
              <label htmlFor="retiredId" className="cursor-pointer">
                <div className="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center hover:border-gray-400">
                  <Upload className="mx-auto h-12 w-12 text-gray-400" />
                  <div className="mt-2">
                    <span className="text-sm text-gray-600">
                      {data.retiredIdFile ? data.retiredIdFile.name : "Click to upload retired ID document"}
                    </span>
                  </div>
                </div>
              </label>
              <input
                id="retiredId"
                type="file"
                className="hidden"
                accept="image/*,.pdf"
                onChange={handleFileUpload}
                required
              />
            </div>
          </div>
        </div>
      )}

      <Button type="submit" className="w-full">
        Continue
      </Button>
    </form>
  )
}
