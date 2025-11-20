import AppLayout from '../../Layouts/AppLayout';
import SiteForm from '../../Components/SiteForm';

export default function Create({ default: defaults }) {
    return (
        <AppLayout title="Provision a WordPress site">
            <section className="space-y-6">
                <div className="card space-y-3">
                    <h2 className="text-2xl font-semibold text-white">Deploy in minutes</h2>
                    <p className="text-sm text-slate-400">
                        Provide your server credentials and database details. We will generate Docker compose files, push
                        them to the remote host, and boot your containerized WordPress stack.
                    </p>
                </div>
                <SiteForm site={defaults} />
            </section>
        </AppLayout>
    );
}
