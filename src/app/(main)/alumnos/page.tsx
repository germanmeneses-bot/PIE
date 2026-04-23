"use client";

import { useState } from "react";

type Alumno = {
  id: number;
  nombre: string;
  rut: string;
  curso: string;
  necesidad: string;
  diagnostico: string;
  estado: "Activo" | "Inactivo";
};

const alumnosData: Alumno[] = [
  { id: 1, nombre: "Camila Rojas Pérez", rut: "21.345.678-9", curso: "3° Básico A", necesidad: "TEA", diagnostico: "Trastorno del Espectro Autista", estado: "Activo" },
  { id: 2, nombre: "Benjamín Torres Soto", rut: "21.456.789-0", curso: "5° Básico B", necesidad: "TDAH", diagnostico: "Trastorno por Déficit de Atención", estado: "Activo" },
  { id: 3, nombre: "Valentina Muñoz García", rut: "21.567.890-1", curso: "1° Medio A", necesidad: "Dificultad Lectora", diagnostico: "Dislexia", estado: "Activo" },
  { id: 4, nombre: "Matías López Fuentes", rut: "21.678.901-2", curso: "4° Básico A", necesidad: "Discapacidad Motora", diagnostico: "Parálisis Cerebral Leve", estado: "Activo" },
  { id: 5, nombre: "Isidora Silva Morales", rut: "21.789.012-3", curso: "2° Básico B", necesidad: "TEL", diagnostico: "Trastorno Específico del Lenguaje", estado: "Activo" },
  { id: 6, nombre: "Nicolás Herrera Jara", rut: "21.890.123-4", curso: "6° Básico A", necesidad: "Discapacidad Visual", diagnostico: "Baja Visión", estado: "Inactivo" },
  { id: 7, nombre: "Antonia Vargas Díaz", rut: "21.901.234-5", curso: "2° Medio B", necesidad: "TDAH", diagnostico: "Trastorno por Déficit de Atención", estado: "Activo" },
];

const necesidades = ["Todos", "TEA", "TDAH", "Dificultad Lectora", "Discapacidad Motora", "TEL", "Discapacidad Visual"];

export default function AlumnosPage() {
  const [search, setSearch] = useState("");
  const [filtroNecesidad, setFiltroNecesidad] = useState("Todos");
  const [showModal, setShowModal] = useState(false);

  const filtered = alumnosData.filter((a) => {
    const matchSearch =
      a.nombre.toLowerCase().includes(search.toLowerCase()) ||
      a.rut.includes(search) ||
      a.curso.toLowerCase().includes(search.toLowerCase());
    const matchNecesidad = filtroNecesidad === "Todos" || a.necesidad === filtroNecesidad;
    return matchSearch && matchNecesidad;
  });

  return (
    <div className="flex-1 overflow-y-auto">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 px-8 py-6">
        <div className="flex items-center justify-between">
          <div>
            <h2 className="text-2xl font-bold text-gray-800">Alumnos PIE</h2>
            <p className="text-sm text-gray-500 mt-1">Gestión de estudiantes del Programa de Integración Escolar</p>
          </div>
          <button
            onClick={() => setShowModal(true)}
            className="flex items-center gap-2 bg-indigo-600 text-white px-4 py-2.5 rounded-lg hover:bg-indigo-700 transition-colors text-sm font-medium"
          >
            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
            </svg>
            Agregar Alumno
          </button>
        </div>
      </div>

      <div className="p-8 space-y-6">
        {/* Filters */}
        <div className="flex flex-col sm:flex-row gap-4">
          <div className="relative flex-1">
            <svg className="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <input
              type="text"
              placeholder="Buscar por nombre, RUT o curso..."
              value={search}
              onChange={(e) => setSearch(e.target.value)}
              className="w-full pl-10 pr-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
            />
          </div>
          <select
            value={filtroNecesidad}
            onChange={(e) => setFiltroNecesidad(e.target.value)}
            className="px-4 py-2.5 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 bg-white"
          >
            {necesidades.map((n) => (
              <option key={n} value={n}>{n}</option>
            ))}
          </select>
        </div>

        {/* Table */}
        <div className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
          <div className="px-6 py-3 bg-gray-50 border-b border-gray-100 text-xs text-gray-500">
            Mostrando {filtered.length} de {alumnosData.length} alumnos
          </div>
          <div className="overflow-x-auto">
            <table className="w-full text-sm">
              <thead>
                <tr className="border-b border-gray-100">
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Nombre</th>
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">RUT</th>
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Curso</th>
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Necesidad</th>
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Diagnóstico</th>
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Estado</th>
                  <th className="text-left px-6 py-4 text-xs font-semibold text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
              </thead>
              <tbody className="divide-y divide-gray-50">
                {filtered.map((alumno) => (
                  <tr key={alumno.id} className="hover:bg-gray-50 transition-colors">
                    <td className="px-6 py-4 font-medium text-gray-800">{alumno.nombre}</td>
                    <td className="px-6 py-4 text-gray-500 font-mono text-xs">{alumno.rut}</td>
                    <td className="px-6 py-4 text-gray-600">{alumno.curso}</td>
                    <td className="px-6 py-4">
                      <span className="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-700">
                        {alumno.necesidad}
                      </span>
                    </td>
                    <td className="px-6 py-4 text-gray-600">{alumno.diagnostico}</td>
                    <td className="px-6 py-4">
                      <span className={`inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${
                        alumno.estado === "Activo"
                          ? "bg-emerald-100 text-emerald-700"
                          : "bg-gray-100 text-gray-500"
                      }`}>
                        {alumno.estado}
                      </span>
                    </td>
                    <td className="px-6 py-4">
                      <div className="flex items-center gap-2">
                        <button className="text-indigo-600 hover:text-indigo-800 text-xs font-medium">Ver</button>
                        <span className="text-gray-300">|</span>
                        <button className="text-gray-500 hover:text-gray-700 text-xs font-medium">Editar</button>
                      </div>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
          {filtered.length === 0 && (
            <div className="text-center py-12 text-gray-400 text-sm">
              No se encontraron alumnos con ese criterio de búsqueda.
            </div>
          )}
        </div>
      </div>

      {/* Modal Agregar Alumno */}
      {showModal && (
        <div className="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
          <div className="bg-white rounded-2xl shadow-xl w-full max-w-lg">
            <div className="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
              <h3 className="text-lg font-semibold text-gray-800">Agregar Alumno PIE</h3>
              <button onClick={() => setShowModal(false)} className="text-gray-400 hover:text-gray-600">
                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
            <div className="p-6 space-y-4">
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Nombre completo</label>
                  <input type="text" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="Juan Pérez" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">RUT</label>
                  <input type="text" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="12.345.678-9" />
                </div>
              </div>
              <div className="grid grid-cols-2 gap-4">
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Curso</label>
                  <select className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    <option>1° Básico A</option>
                    <option>2° Básico A</option>
                    <option>3° Básico A</option>
                    <option>4° Básico A</option>
                    <option>5° Básico A</option>
                    <option>6° Básico A</option>
                    <option>1° Medio A</option>
                    <option>2° Medio A</option>
                  </select>
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-600 mb-1.5">Necesidad educativa</label>
                  <select className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                    {necesidades.filter((n) => n !== "Todos").map((n) => (
                      <option key={n}>{n}</option>
                    ))}
                  </select>
                </div>
              </div>
              <div>
                <label className="block text-xs font-semibold text-gray-600 mb-1.5">Diagnóstico</label>
                <textarea rows={2} className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 resize-none" placeholder="Describe el diagnóstico del alumno..." />
              </div>
            </div>
            <div className="px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
              <button onClick={() => setShowModal(false)} className="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 font-medium">Cancelar</button>
              <button onClick={() => setShowModal(false)} className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">Guardar Alumno</button>
            </div>
          </div>
        </div>
      )}
    </div>
  );
}
