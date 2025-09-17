import { NextResponse } from "next/server"
import type { NextRequest } from "next/server"
import { verifyToken } from "@/lib/auth/session"

export async function middleware(request: NextRequest) {
  // Get the pathname of the request (e.g. /, /protected)
  const path = request.nextUrl.pathname

  // Define public paths that don't require authentication
  const publicPaths = ["/", "/apply", "/auth/login", "/auth/sign-up", "/application-submitted"]

  // Check if the path is public
  const isPublicPath = publicPaths.some((publicPath) => path === publicPath || path.startsWith(publicPath))

  // Get the session token from cookies
  const token = request.cookies.get("session-token")?.value

  // If accessing a protected route without a token, redirect to login
  if (!isPublicPath && !token) {
    return NextResponse.redirect(new URL("/auth/login", request.url))
  }

  // If we have a token, verify it
  if (token) {
    const userId = await verifyToken(token)

    // If token is invalid, clear it and redirect to login for protected routes
    if (!userId) {
      const response = NextResponse.redirect(new URL("/auth/login", request.url))
      response.cookies.delete("session-token")
      return response
    }

    // If user is authenticated and trying to access auth pages, redirect to account
    if (isPublicPath && (path.startsWith("/auth/") || path === "/apply")) {
      return NextResponse.redirect(new URL("/account", request.url))
    }
  }

  return NextResponse.next()
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     * - api routes that handle their own auth
     */
    "/((?!_next/static|_next/image|favicon.ico|api|.*\\.(?:svg|png|jpg|jpeg|gif|webp)$).*)",
  ],
}
