"use client";

import { useState } from "react";

export default function PerfilPage() {
  const [editMode, setEditMode] = useState(false);
  const [nombre, setNombre] = useState("María Pérez González");
  const [email, setEmail] = useState("mperez@colegiopie.cl");
  const [telefono, setTelefono] = useState("+56 9 8765 4321");
  const [cargo, setCargo] = useState("Profesora de Educación Diferencial");
  const [especialidad, setEspecialidad] = useState("Trastornos de Aprendizaje");
  const [rut, setRut] = useState("12.345.678-9");

  const actividades = [
    { accion: "Informe creado", detalle: "Informe Psicopedagógico — Camila Rojas", fecha: "Hace 2 horas" },
    { accion: "Alumno actualizado", detalle: "Datos actualizados de Benjamín Torres", fecha: "Ayer" },
    { accion: "Informe completado", detalle: "Plan de Apoyo Individual — Valentina Muñoz", fecha: "Hace 3 días" },
    { accion: "Alumno registrado", detalle: "Nuevo alumno: Isidora Silva Morales", fecha: "Hace 5 días" },
  ];

  return (
    <div className="flex-1 overflow-y-auto">
      {/* Header */}
      <div className="bg-white border-b border-gray-200 px-8 py-6">
        <h2 className="text-2xl font-bold text-gray-800">Mi Perfil</h2>
        <p className="text-sm text-gray-500 mt-1">Información personal y configuración de cuenta</p>
      </div>

      <div className="p-8">
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6">
          {/* Left column — avatar + summary */}
          <div className="space-y-5">
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6 flex flex-col items-center text-center">
              <div className="w-24 h-24 bg-indigo-600 rounded-full flex items-center justify-center text-white text-3xl font-bold mb-4">
                MP
              </div>
              <h3 className="text-lg font-bold text-gray-800">{nombre}</h3>
              <p className="text-sm text-indigo-600 font-medium mt-1">{cargo}</p>
              <p className="text-xs text-gray-400 mt-1">{rut}</p>
              <div className="mt-4 w-full pt-4 border-t border-gray-50 space-y-2 text-sm text-gray-500">
                <div className="flex items-center gap-2 justify-center">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                  </svg>
                  <span className="truncate">{email}</span>
                </div>
                <div className="flex items-center gap-2 justify-center">
                  <svg className="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                  </svg>
                  <span>{telefono}</span>
                </div>
              </div>
            </div>

            {/* Stats */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-5 space-y-3">
              <h4 className="text-sm font-semibold text-gray-700">Resumen de actividad</h4>
              {[
                { label: "Alumnos atendidos", value: "42" },
                { label: "Informes creados", value: "18" },
                { label: "Cursos asignados", value: "6" },
              ].map((s) => (
                <div key={s.label} className="flex justify-between items-center text-sm">
                  <span className="text-gray-500">{s.label}</span>
                  <span className="font-bold text-gray-800">{s.value}</span>
                </div>
              ))}
            </div>
          </div>

          {/* Right column — form + activity */}
          <div className="lg:col-span-2 space-y-6">
            {/* Edit form */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm">
              <div className="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h3 className="text-base font-semibold text-gray-800">Información Personal</h3>
                <button
                  onClick={() => setEditMode(!editMode)}
                  className={`flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium transition-colors ${
                    editMode
                      ? "bg-gray-100 text-gray-600 hover:bg-gray-200"
                      : "bg-indigo-50 text-indigo-600 hover:bg-indigo-100"
                  }`}
                >
                  {editMode ? (
                    <>
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                      </svg>
                      Cancelar
                    </>
                  ) : (
                    <>
                      <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                      </svg>
                      Editar
                    </>
                  )}
                </button>
              </div>
              <div className="p-6 grid grid-cols-1 sm:grid-cols-2 gap-5">
                {[
                  { label: "Nombre completo", value: nombre, setter: setNombre, type: "text" },
                  { label: "RUT", value: rut, setter: setRut, type: "text" },
                  { label: "Correo electrónico", value: email, setter: setEmail, type: "email" },
                  { label: "Teléfono", value: telefono, setter: setTelefono, type: "tel" },
                  { label: "Cargo", value: cargo, setter: setCargo, type: "text" },
                  { label: "Especialidad", value: especialidad, setter: setEspecialidad, type: "text" },
                ].map((field) => (
                  <div key={field.label}>
                    <label className="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">{field.label}</label>
                    {editMode ? (
                      <input
                        type={field.type}
                        value={field.value}
                        onChange={(e) => field.setter(e.target.value)}
                        className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
                      />
                    ) : (
                      <p className="text-sm text-gray-800 font-medium">{field.value}</p>
                    )}
                  </div>
                ))}
              </div>
              {editMode && (
                <div className="px-6 py-4 border-t border-gray-100 flex justify-end">
                  <button
                    onClick={() => setEditMode(false)}
                    className="px-5 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors"
                  >
                    Guardar cambios
                  </button>
                </div>
              )}
            </div>

            {/* Recent activity */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm">
              <div className="px-6 py-4 border-b border-gray-100">
                <h3 className="text-base font-semibold text-gray-800">Actividad Reciente</h3>
              </div>
              <div className="divide-y divide-gray-50">
                {actividades.map((act, i) => (
                  <div key={i} className="flex items-start gap-4 px-6 py-4">
                    <div className="w-2 h-2 rounded-full bg-indigo-400 mt-2 flex-shrink-0" />
                    <div className="flex-1 min-w-0">
                      <p className="text-sm font-medium text-gray-700">{act.accion}</p>
                      <p className="text-xs text-gray-400 mt-0.5">{act.detalle}</p>
                    </div>
                    <p className="text-xs text-gray-400 flex-shrink-0">{act.fecha}</p>
                  </div>
                ))}
              </div>
            </div>

            {/* Change password */}
            <div className="bg-white rounded-xl border border-gray-100 shadow-sm p-6">
              <h3 className="text-base font-semibold text-gray-800 mb-4">Cambiar Contraseña</h3>
              <div className="space-y-4 max-w-sm">
                <div>
                  <label className="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Contraseña actual</label>
                  <input type="password" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="••••••••" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Nueva contraseña</label>
                  <input type="password" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="••••••••" />
                </div>
                <div>
                  <label className="block text-xs font-semibold text-gray-500 mb-1.5 uppercase tracking-wider">Confirmar contraseña</label>
                  <input type="password" className="w-full border border-gray-200 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500" placeholder="••••••••" />
                </div>
                <button className="px-4 py-2 bg-indigo-600 text-white text-sm font-medium rounded-lg hover:bg-indigo-700 transition-colors">
                  Actualizar contraseña
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  );
}
