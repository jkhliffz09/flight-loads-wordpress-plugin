import { createClient } from "@/lib/supabase/server"
import { Header } from "@/components/layout/header"
import { Button } from "@/components/ui/button"
import Link from "next/link"

export default async function HomePage() {
  const supabase = createClient()
  const {
    data: { user },
  } = await supabase.auth.getUser()

  return (
    <div className="min-h-screen bg-gray-50">
      <Header />

      <main className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div className="text-center">
          <h1 className="text-4xl font-bold text-gray-900 sm:text-6xl">Welcome to Passrider</h1>
          <p className="mt-6 text-lg leading-8 text-gray-600">
            Access flight load information and connect with airline professionals worldwide.
          </p>

          <div className="mt-10 flex items-center justify-center gap-x-6">
            {user ? (
              <>
                <Button asChild size="lg">
                  <Link href="/requests">View Flight Requests</Link>
                </Button>
                <Button variant="outline" size="lg" asChild>
                  <Link href="/account">My Account</Link>
                </Button>
              </>
            ) : (
              <>
                <Button asChild size="lg">
                  <Link href="/apply">Apply Now</Link>
                </Button>
                <Button variant="outline" size="lg" asChild>
                  <Link href="/auth/login">Sign In</Link>
                </Button>
              </>
            )}
          </div>
        </div>
      </main>
    </div>
  )
}
