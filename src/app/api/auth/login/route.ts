import { NextResponse } from "next/server";
import { db } from "@/db";
import { users } from "@/db/schema";
import { eq } from "drizzle-orm";

export async function POST(request: Request) {
  try {
    const { username, password } = await request.json();

    if (!username || !password) {
      return NextResponse.json(
        { error: "Usuario y contraseña son requeridos" },
        { status: 400 }
      );
    }

    // Buscar usuario en BD
    const result = await db
      .select()
      .from(users)
      .where(eq(users.username, username))
      .limit(1);

    const user = result[0];

    if (!user || user.password !== password) {
      return NextResponse.json(
        { error: "Usuario o contraseña incorrectos" },
        { status: 401 }
      );
    }

    // Crear respuesta con cookie de sesión
    const response = NextResponse.json({
      ok: true,
      user: { id: user.id, name: user.name, role: user.role, username: user.username },
    });

    // Cookie simple de sesión (válida 8 horas)
    const sessionData = JSON.stringify({
      userId: user.id,
      username: user.username,
      name: user.name,
      role: user.role,
    });

    response.cookies.set("pie_session", Buffer.from(sessionData).toString("base64"), {
      httpOnly: true,
      secure: process.env.NODE_ENV === "production",
      sameSite: "lax",
      maxAge: 60 * 60 * 8, // 8 horas
      path: "/",
    });

    return response;
  } catch (error) {
    console.error("Login error:", error);
    return NextResponse.json(
      { error: "Error interno del servidor" },
      { status: 500 }
    );
  }
}
