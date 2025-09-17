"use client"

import { useState } from "react"
import { Card, CardContent, CardHeader, CardTitle } from "@/components/ui/card"
import { Progress } from "@/components/ui/progress"
import { BasicInformationStep } from "./basic-information-step"
import { EmailVerificationStep } from "./email-verification-step"
import { CreateAccountStep } from "./create-account-step"

export interface ApplicationData {
  // Step 1: Basic Information
  fullName: string
  airlineId: number | null
  airlineName: string
  status: "active" | "retired"
  // Retired fields
  phoneNumber?: string
  retirementDate?: string
  exAirlineJob?: string
  yearsWorked?: number
  retiredIdFile?: File
  // Step 2: Email Verification (Active only)
  airlineEmail?: string
  verificationCode?: string
  isEmailVerified?: boolean
  // Step 3: Account Creation
  username: string
  email: string
  password: string
  confirmPassword: string
}

export function MultiStepApplicationForm() {
  const [currentStep, setCurrentStep] = useState(1)
  const [applicationData, setApplicationData] = useState<ApplicationData>({
    fullName: "",
    airlineId: null,
    airlineName: "",
    status: "active",
    username: "",
    email: "",
    password: "",
    confirmPassword: "",
  })

  const updateApplicationData = (data: Partial<ApplicationData>) => {
    setApplicationData((prev) => ({ ...prev, ...data }))
  }

  const nextStep = () => {
    if (currentStep === 1) {
      // If retired, skip email verification step
      if (applicationData.status === "retired") {
        setCurrentStep(3)
      } else {
        setCurrentStep(2)
      }
    } else {
      setCurrentStep((prev) => prev + 1)
    }
  }

  const prevStep = () => {
    if (currentStep === 3 && applicationData.status === "retired") {
      // If retired and on step 3, go back to step 1
      setCurrentStep(1)
    } else {
      setCurrentStep((prev) => prev - 1)
    }
  }

  const getProgress = () => {
    if (applicationData.status === "retired") {
      // For retired users: Step 1 -> Step 3 (skip step 2)
      return currentStep === 1 ? 50 : 100
    } else {
      // For active users: Step 1 -> Step 2 -> Step 3
      return (currentStep / 3) * 100
    }
  }

  const getStepTitle = () => {
    switch (currentStep) {
      case 1:
        return "Basic Information"
      case 2:
        return "Email Verification"
      case 3:
        return "Create Account"
      default:
        return ""
    }
  }

  return (
    <Card className="w-full">
      <CardHeader>
        <div className="flex justify-between items-center mb-4">
          <CardTitle>
            Step {currentStep === 2 ? 2 : currentStep === 3 ? (applicationData.status === "retired" ? 2 : 3) : 1} of{" "}
            {applicationData.status === "retired" ? 2 : 3}
          </CardTitle>
          <span className="text-sm text-gray-500">{getStepTitle()}</span>
        </div>
        <Progress value={getProgress()} className="w-full" />
      </CardHeader>
      <CardContent>
        {currentStep === 1 && (
          <BasicInformationStep data={applicationData} updateData={updateApplicationData} onNext={nextStep} />
        )}
        {currentStep === 2 && (
          <EmailVerificationStep
            data={applicationData}
            updateData={updateApplicationData}
            onNext={nextStep}
            onPrev={prevStep}
          />
        )}
        {currentStep === 3 && (
          <CreateAccountStep data={applicationData} updateData={updateApplicationData} onPrev={prevStep} />
        )}
      </CardContent>
    </Card>
  )
}
