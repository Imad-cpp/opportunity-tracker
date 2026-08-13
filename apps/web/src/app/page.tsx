const stack = ["Next.js 16.2", "TypeScript", "Laravel 13", "PostgreSQL 18"];

export default function Home() {
  return (
    <main className="shell">
      <section className="panel" aria-labelledby="page-title">
        <p className="eyebrow">Opportunity Tracker</p>
        <h1 id="page-title">Application workspace scaffold</h1>
        <p className="lede">
          The web and API boundaries are running. Product workflows are implemented in later roadmap steps.
        </p>
        <ul className="stack" aria-label="Application stack">
          {stack.map((item) => (
            <li key={item}>{item}</li>
          ))}
        </ul>
      </section>
    </main>
  );
}
