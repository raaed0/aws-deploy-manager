import { Head, Link, usePage } from '@inertiajs/react';
import FlashBanner from '../Components/FlashBanner';

export default function AppLayout({ title = null, actions = null, children }) {
    const { url } = usePage();
    const { app } = usePage().props;

    return (
        <>
            {title ? <Head title={title} /> : null}
            <div className="min-h-screen bg-slate-950 text-slate-100">
                <header className="border-b border-white/5 bg-slate-950/80 backdrop-blur">
                    <div className="mx-auto flex max-w-5xl items-center justify-between px-6 py-6">
                        <div>
                            <p className="text-sm uppercase tracking-widest text-slate-400">WordPress Ops</p>
                            <h1 className="text-2xl font-semibold tracking-tight text-white">
                                {title ?? app?.name ?? 'Manager'}
                            </h1>
                        </div>
                        <nav className="flex items-center gap-4 text-sm font-medium text-slate-300">
                            <Link
                                href="/sites"
                                className={`rounded-full px-4 py-2 transition ${
                                    url.startsWith('/sites')
                                        ? 'bg-sky-500/20 text-sky-300 ring-1 ring-sky-500/40'
                                        : 'hover:text-white'
                                }`}
                            >
                                Sites
                            </Link>
                            <Link
                                href="/sites/create"
                                className="rounded-full border border-slate-700/80 px-4 py-2 text-slate-50 transition hover:border-sky-500/30 hover:bg-slate-800/40"
                            >
                                New Site
                            </Link>
                        </nav>
                    </div>
                </header>

                <FlashBanner />

                <main className="mx-auto max-w-5xl px-6 py-10">
                    {actions ? <div className="mb-6 flex items-center justify-between">{actions}</div> : null}
                    <div className="space-y-6">{children}</div>
                </main>
            </div>
        </>
    );
}
