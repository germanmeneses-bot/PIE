"use client";

import { useState } from "react";

type Informe = {
  id: number;
  titulo: string;
  alumno: string;
  tipo: string;
  fecha: string;
  estado: "Borrador" | "Completado" | "Pendiente";
};

const informesData: Informe[] = [
  { id: 1, titulo: "Informe Psicopedagógico — 1er Semestre", alumno: "Camila Rojas Pérez", tipo: "Psicopedagógico", fecha: "2026-03-15", estado: "Completado" },
  { id: 2, titulo: "Informe de Avance PIE", alumno: "Benjamín Torres Soto", tipo: "Avance", fecha: "2026-04-01", estado: "Completado" },
  { id: 3, titulo: "Plan de Apoyo Individual", alumno: "Valentina Muñoz García", tipo: "PAI", fecha: "2026-04-10", estado: "Borrador" },
  { id: 4, titulo: "Informe de Seguimiento", alumno: "Matías López Fuentes", tipo: "Seguimiento", fecha: "2026-04-20", estado: "Pendiente" },
  { id: 5, titulo: "Evaluación de Necesidades", alumno: "Isidora Silva Morales", tipo: "Evaluación", fecha: "2026-04-22", estado: "Borrador" },
];

const tiposInforme = ["Psicopedagógico", "Avance", "PAI", "Seguimiento", "Evaluación", "Derivación"];

const estadoColors: Record<string, string> = {
  Completado: "bg-emerald-100 text-emerald-700",
  Borrador: "bg-amber-100 text-amber-700",
  Pendiente: "bg-rose-100 text-rose-700",
};

export default function InformesPage() {
  const [showModal, setShowModal] = useState(false);
  const [tipoSeleccionado, setTipoSeleccionado] = useState("Psicopedagógico");

  return (
    <div className="flex-1 overflow-y-auto">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 px-8 py-6">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-2xl font-bold text-gray-800">Informes</h2>
            <p className="text-sm text-gray-500 mt-1">Creación y seguimiento de informes del PIE</p>
          </div>
          <button
            onClick={() => setShowModal(true)}
            className="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Crear Informe
          </button>
        </div>
      </div>

      <div className="p-8 space-y-6">
        {/* Stats */}
        <div className="grid grid-cols-3 gap-5">
          {[
            { label: "Completados", value: informesData.filter((i) => i.estado === "Completado").length, color: "bg-emerald-50 text-emerald-700 border-emerald-100" },
            { label: "Borradores", value: informesData.filter((i) => i.estado === "Borrador").length, color: "bg-amber-50 text-amber-700 border-amber-100" },
            { label: "Pendientes", value: informesData.filter((i) => i.estado === "Pendiente").length, color: "bg-rose-50 text-rose-700 border-rose-100" },
          ].map((s) => (
            <div key={s.label} className={`rounded-xl border p-5 ${s.color}`}>
              <p className="text-3xl font-bold">{s.value}</p>
              <p className="text-sm font-medium mt-0.5">{s.label}</p>
            </div>
          ))}
        </div>

        {/* Types of reports */}
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
          <h3 className="text-sm font-semibold text-gray-700 mb-4">Tipos de Informe Disponibles</h3>
          <div className="flex flex-wrap gap-2">
            {tiposInforme.map((tipo) => (
              <span key={tipo} className="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium bg-indigo-50 text-indigo-700 border border-indigo-100">
                {tipo}
              </span>
            ))}
          </div>
        </div>

        {/* Reports list */}
        <div className="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
          <div className="px-6 py-4 border-b border-gray-100">
            <h3 className="text-base font-semibold text-gray-800">Informes Recientes</h3>
          </div>
          <div className="divide-y divide-gray-50">
            {informesData.map((informe) => (
              <div key={informe.id} className="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
                <div className="w-10 h-10 bg-indigo-50 rounded-lg flex items-center justify-center flex-shrink-0">
                  <svg className="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                  </svg>
                </div>
                <div className="flex-1 min-w-0">
                  <p className="text-sm font-semibold text-gray-800 truncate">{informe.titulo}</p>
                  <p className="text-xs text-gray-500 mt-0.5">{informe.alumno} · {informe.tipo}</p>
                </div>
                <div className="flex items-center gap-4 flex-shrink-0">
                  <p className="text-xs text-gray-400">{new Date(informe.fecha).toLocaleDateString("es-CL")}</p>
                  <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${estadoColors[informe.estado]}`}>
                    {informe.estado}
                  </span>
                  <div className="flex gap-2">
                    <button className="text-xs text-indigo-600 hover:text-indigo-800 font-medium">Abrir</button>
                    <span className="text-gray-300">|</span>
                    <button className="text-xs text-gray-500 hover:text-gray-700 font-medium">Descargar</button>
                  </div>
                </div>
              </div>
            ))}
          </div>
        </div>
      </div>

      {/* Modal Crear Informe */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
            <div className="px-6 py-5 border-b border-gray-100 flex items-center justify-between sticky top-0 bg-white">
              <h3 className="text-lg font-semibold text-gray-800">Crear Nuevo Informe</h3>
              <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-6 space-y-5">
              {/* Tipo selector */}
              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-2">Tipo de Informe</label>
                <div className="grid grid-cols-3 gap-2">
                  {tiposInforme.map((tipo) => (
                    <button
                      key={tipo}
                      onClick={() => setTipoSeleccionado(tipo)}
                      className={`px-3 py-2 rounded-lg text-xs font-medium border transition-colors ${
                        tipoSeleccionado === tipo
                          ? "bg-indigo-600 text-white border-indigo-600"
                          : "bg-white text-gray-600 border-gray-200 hover:border-indigo-300"
                      }`}
                    >
                      {tipo}
                    </button>
                  ))}
                </div>
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Alumno</label>
                  <select className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option>Camila Rojas Pérez</option>
                    <option>Benjamín Torres Soto</option>
                    <option>Valentina Muñoz García</option>
                    <option>Matías López Fuentes</option>
                    <option>Isidora Silva Morales</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Fecha</label>
                  <input type="date" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" defaultValue="2026-04-23" />
                </div>
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1.5">Título del Informe</label>
                <input type="text" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder={`Informe ${tipoSeleccionado} — 2026`} />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1.5">Antecedentes del Alumno</label>
                <textarea rows={3} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Describe los antecedentes relevantes del estudiante..." />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1.5">Descripción / Contenido del Informe</label>
                <textarea rows={5} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Ingresa el contenido del informe, observaciones, evaluaciones y recomendaciones..." />
              </div>

              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1.5">Objetivos / Metas</label>
                <textarea rows={3} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Lista los objetivos de apoyo establecidos..." />
              </div>

              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Profesional a cargo</label>
                  <input type="text" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" defaultValue="María Pérez" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Estado</label>
                  <select className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option>Borrador</option>
                    <option>Completado</option>
                    <option>Pendiente</option>
                  </select>
                </div>
              </div>
            </div>
            <div className="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 sticky bottom-0 bg-white">
              <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium">Cancelar</button>
              <button onClick={() => setShowModal(false)} className="px-4 py-2 bg-gray-100 text-gray-700 text-sm font-medium rounded-lg hover:bg-gray-200 transition-colors">Guardar Borrador</button>
              <button onClick={() => setShowModal(false)} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">Crear Informe</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
