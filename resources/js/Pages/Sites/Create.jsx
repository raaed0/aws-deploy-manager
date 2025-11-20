import AppLayout from '../../Layouts/AppLayout';
import SiteForm from '../../Components/SiteForm';

export default function Create({ default: defaults }) {
    return (
        <AppLayout title="Provision a WordPress site">
            <section className="space-y-6">
                <div className="card space-y-3">
                    <h2 className="text-2xl font-semibold text-white">Deploy in minutes</h2>
                    <p className="text-sm text-slate-400">
                        Pick a project name, domain, AZ, and WordPress image. We will wire the Docker host, database,
                        and monitoring for you.
                    </p>
                </div>
                <SiteForm site={defaults} />
            </section>
        </AppLayout>
    );
}
