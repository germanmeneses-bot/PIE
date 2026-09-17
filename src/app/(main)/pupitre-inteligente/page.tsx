const STILLS = [
  {
    src: "/images/pupitre-inteligente/real/real-01a-aula.jpg",
    alt: "Aula con estudiantes en movimiento natural",
  },
  {
    src: "/images/pupitre-inteligente/real/real-02a-profe.jpg",
    alt: "Profesora en la pizarra con micrófonos",
  },
  {
    src: "/images/pupitre-inteligente/real/real-03a-nina.jpg",
    alt: "Niña con audífono en el pupitre",
  },
  {
    src: "/images/pupitre-inteligente/real/real-04a-pupitre.jpg",
    alt: "Detalle del pupitre y micrófono de superficie",
  },
  {
    src: "/images/pupitre-inteligente/real/real-04b-paneles.jpg",
    alt: "Paneles microperforados bajo la mesa",
  },
  {
    src: "/images/pupitre-inteligente/real/real-05a-audifono.jpg",
    alt: "Audífono del estudiante",
  },
  {
    src: "/images/pupitre-inteligente/real/real-06a-cierre.jpg",
    alt: "Vista general del aula inclusiva",
  },
];

export default function PupitreInteligentePage() {
  return (
    <div className="p-8 max-w-5xl mx-auto space-y-8">
      <header className="space-y-2">
        <p className="text-sm text-indigo-600 font-medium uppercase tracking-wide">
          Apoyo auditivo en aula
        </p>
        <h1 className="text-3xl font-bold text-slate-900">
          Pupitre inteligente
        </h1>
        <p className="text-slate-600 max-w-2xl">
          Video silencioso (solo imágenes) con movimiento natural de las
          personas: estudiantes, profesora y la niña con audífono en un aula
          con mucho boche. Sin efectos 3D artificiales. El pupitre escolar
          común incorpora micrófono de superficie, paneles microperforados,
          cancelación de ruido y conexión al audífono, con micrófonos en la
          pizarra priorizando la voz del profesor.
        </p>
      </header>

      <section className="space-y-3">
        <h2 className="text-lg font-semibold text-slate-800">Video</h2>
        <div className="rounded-xl overflow-hidden bg-slate-900 shadow-lg aspect-video">
          <video
            className="w-full h-full"
            controls
            playsInline
            preload="metadata"
            poster="/images/pupitre-inteligente/real/real-01a-aula.jpg"
            src="/videos/pupitre-inteligente-aula.mp4"
          >
            Tu navegador no soporta la reproducción de video.
          </video>
        </div>
        <p className="text-sm text-slate-500">
          Sin narración hablada · 1920×1080 · movimiento natural de personas
        </p>
      </section>

      <section className="grid sm:grid-cols-2 gap-4 text-sm text-slate-700">
        <div className="space-y-1">
          <h3 className="font-semibold text-slate-900">Dimensiones</h3>
          <ul className="list-disc list-inside space-y-0.5">
            <li>Superficie: 60 cm de ancho</li>
            <li>Alto: 75 cm</li>
            <li>Ancho entre patas: 70 cm</li>
          </ul>
        </div>
        <div className="space-y-1">
          <h3 className="font-semibold text-slate-900">Características</h3>
          <ul className="list-disc list-inside space-y-0.5">
            <li>Mesa y patas de fierro (pupitre común)</li>
            <li>Superficie ergonómica de trabajo</li>
            <li>Paneles microperforados bajo la mesa</li>
            <li>Micrófono direccional ambiental en superficie</li>
            <li>Cancelación de ruido activa</li>
            <li>Conectividad con audífono</li>
            <li>Micrófonos alrededor de la pizarra</li>
          </ul>
        </div>
      </section>

      <section className="space-y-3">
        <h2 className="text-lg font-semibold text-slate-800">Fotogramas</h2>
        <div className="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
          {STILLS.map((still) => (
            // eslint-disable-next-line @next/next/no-img-element
            <img
              key={still.src}
              src={still.src}
              alt={still.alt}
              className="w-full aspect-video object-cover rounded-lg border border-slate-200"
            />
          ))}
        </div>
      </section>
    </div>
  );
}
