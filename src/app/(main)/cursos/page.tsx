const cursos = [
  { id: 1, nivel: "1° Básico A", jefatura: "Ana González", alumnos: 32, pie: 3, horario: "Lunes - Viernes 08:00–13:00" },
  { id: 2, nivel: "2° Básico A", jefatura: "Pedro Ramírez", alumnos: 30, pie: 2, horario: "Lunes - Viernes 08:00–13:00" },
  { id: 3, nivel: "2° Básico B", jefatura: "Luisa Castillo", alumnos: 31, pie: 3, horario: "Lunes - Viernes 08:00–13:00" },
  { id: 4, nivel: "3° Básico A", jefatura: "Carlos Ibáñez", alumnos: 33, pie: 4, horario: "Lunes - Viernes 08:00–13:30" },
  { id: 5, nivel: "4° Básico A", jefatura: "Mónica Vega", alumnos: 29, pie: 5, horario: "Lunes - Viernes 08:00–13:30" },
  { id: 6, nivel: "5° Básico A", jefatura: "Roberto Salinas", alumnos: 35, pie: 3, horario: "Lunes - Viernes 08:00–14:00" },
  { id: 7, nivel: "5° Básico B", jefatura: "Carmen Flores", alumnos: 34, pie: 4, horario: "Lunes - Viernes 08:00–14:00" },
  { id: 8, nivel: "6° Básico A", jefatura: "Andrés Mora", alumnos: 36, pie: 2, horario: "Lunes - Viernes 08:00–14:00" },
  { id: 9, nivel: "7° Básico A", jefatura: "Patricia Leal", alumnos: 38, pie: 5, horario: "Lunes - Viernes 08:00–14:30" },
  { id: 10, nivel: "8° Básico A", jefatura: "Jorge Espinoza", alumnos: 37, pie: 3, horario: "Lunes - Viernes 08:00–14:30" },
  { id: 11, nivel: "1° Medio A", jefatura: "Claudia Ramos", alumnos: 40, pie: 4, horario: "Lunes - Viernes 08:00–15:15" },
  { id: 12, nivel: "2° Medio B", jefatura: "Fernando Navarro", alumnos: 39, pie: 5, horario: "Lunes - Viernes 08:00–15:15" },
];

const levelColors: Record<string, string> = {
  "Básico": "bg-sky-100 text-sky-700",
  "Medio": "bg-violet-100 text-violet-700",
};

function getLevelColor(nivel: string) {
  if (nivel.includes("Básico")) return levelColors["Básico"];
  if (nivel.includes("Medio")) return levelColors["Medio"];
  return "bg-gray-100 text-gray-600";
}

export default function CursosPage() {
  const totalPie = cursos.reduce((acc, c) => acc + c.pie, 0);
  const totalAlumnos = cursos.reduce((acc, c) => acc + c.alumnos, 0);

  return (
    <div className="flex-1 overflow-y-auto">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 px-8 py-6">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-2xl font-bold text-gray-800">Cursos</h2>
            <p className="text-sm text-gray-500 mt-1">Listado de cursos y estudiantes PIE por nivel</p>
          </div>
        </div>
      </div>

      <div className="p-8 space-y-6">
        {/* Summary cards */}
        <div className="grid grid-cols-1 sm:grid-cols-3 gap-6">
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div className="w-11 h-11 bg-emerald-100 rounded-xl flex items-center justify-center">
              <svg className="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
              </svg>
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-800">{cursos.length}</p>
              <p className="text-xs text-gray-500">Cursos activos</p>
            </div>
          </div>
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div className="w-11 h-11 bg-sky-100 rounded-xl flex items-center justify-center">
              <svg className="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
              </svg>
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-800">{totalAlumnos}</p>
              <p className="text-xs text-gray-500">Total alumnos</p>
            </div>
          </div>
          <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
            <div className="w-11 h-11 bg-indigo-100 rounded-xl flex items-center justify-center">
              <svg className="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
              </svg>
            </div>
            <div>
              <p className="text-2xl font-bold text-gray-800">{totalPie}</p>
              <p className="text-xs text-gray-500">Alumnos PIE total</p>
            </div>
          </div>
        </div>

        {/* Cards grid */}
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
          {cursos.map((curso) => (
            <div key={curso.id} className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
              <div className="flex items-start justify-between mb-3">
                <div>
                  <span className={`inline-flex items-center px-2 py-0.5 rounded text-xs font-medium mb-2 ${getLevelColor(curso.nivel)}`}>
                    {curso.nivel.includes("Básico") ? "Enseñanza Básica" : "Enseñanza Media"}
                  </span>
                  <h4 className="text-base font-bold text-gray-800">{curso.nivel}</h4>
                </div>
                <span className="w-9 h-9 bg-indigo-50 rounded-lg flex items-center justify-center text-indigo-700 font-bold text-sm">
                  {curso.pie}
                </span>
              </div>
              <div className="space-y-1.5 text-sm text-gray-500">
                <div className="flex items-center gap-2">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                  </svg>
                  <span>Jefatura: {curso.jefatura}</span>
                </div>
                <div className="flex items-center gap-2">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                  </svg>
                  <span>{curso.alumnos} alumnos totales</span>
                </div>
                <div className="flex items-center gap-2">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                  <span className="text-xs">{curso.horario}</span>
                </div>
              </div>
              <div className="mt-4 pt-3 border-t border-gray-50 flex justify-between items-center">
                <span className="text-xs text-indigo-600 font-medium">{curso.pie} alumno{curso.pie !== 1 ? "s" : ""} PIE</span>
                <button className="text-xs text-gray-500 hover:text-indigo-600 font-medium transition-colors">Ver detalle →</button>
              </div>
            </div>
          ))}
        </div>
      </div>
    </div>
  );
}
