/**
 * Seed inicial: crea usuario admin por defecto.
 * Ejecutar solo después de las migraciones.
 */
import { db } from "./index";
import { users } from "./schema";

async function seed() {
  // Insertar usuario admin si no existe
  const existing = await db.select().from(users);
  if (existing.length === 0) {
    await db.insert(users).values({
      username: "admin",
      password: "admin",
      name: "Administrador",
      role: "admin",
    });
    console.log("Usuario admin creado: admin / admin");
  } else {
    console.log("Ya existen usuarios, seed omitido.");
  }
}

seed().catch(console.error);
