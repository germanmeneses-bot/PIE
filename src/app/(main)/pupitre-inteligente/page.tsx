const STILLS = [
  {
    src: "/images/pupitre-inteligente/01-aula-boche.jpg",
    alt: "Aula con mucho boche y estudiantes",
  },
  {
    src: "/images/pupitre-inteligente/02-pizarra-microfonos.jpg",
    alt: "Micrófonos alrededor de la pizarra",
  },
  {
    src: "/images/pupitre-inteligente/03-nina-pupitre.jpg",
    alt: "Niña con audífono en el pupitre inteligente",
  },
  {
    src: "/images/pupitre-inteligente/04-detalle-pupitre.jpg",
    alt: "Detalle del pupitre: mesa, patas de fierro y micrófono",
  },
  {
    src: "/images/pupitre-inteligente/05-paneles-microperforados.jpg",
    alt: "Paneles microperforados bajo la superficie",
  },
  {
    src: "/images/pupitre-inteligente/06-comunicacion-priorizada.jpg",
    alt: "Comunicación priorizada profesor–estudiante",
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
          Video silencioso (solo imágenes) de un pupitre escolar común con
          tecnología de apoyo para estudiantes con discapacidad auditiva:
          micrófonos en la pizarra, cancelación de ruido activa y conexión
          directa al audífono, priorizando la voz del profesor en un aula con
          mucho boche.
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
            poster="/images/pupitre-inteligente/01-aula-boche.jpg"
            src="/videos/pupitre-inteligente-aula.mp4"
          >
            Tu navegador no soporta la reproducción de video.
          </video>
        </div>
        <p className="text-sm text-slate-500">
          Sin narración hablada · 26 s · 1920×1080
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
