import { useForm } from '@inertiajs/react';
import FormField from './FormField';
import Input from './Input';
import Select from './Select';

const defaultValues = {
    name: '',
    domain: '',
    container_name: '',
    availability_zone: 'us-east-1',
    docker_image: 'wordpress:latest',
};

const azOptions = ['us-east-1', 'us-east-1a', 'us-east-1b', 'us-east-1c', 'us-east-1d'];

const imageOptions = [
    'wordpress:latest',
    'wordpress:6.6-php8.2-apache',
    'wordpress:6.6-php8.2-fpm',
    'wordpress:6.5-php8.1-apache',
];

export default function SiteForm({ site = null }) {
    const initialData = { ...defaultValues, ...site };
    const { data, setData, errors, processing, post, put } = useForm(initialData);

    const submit = (event) => {
        event.preventDefault();

        if (site?.id) {
            put(`/sites/${site.id}`);
        } else {
            post('/sites');
        }
    };

    return (
        <form onSubmit={submit} className="space-y-8">
            <section className="card space-y-4">
                <p className="text-sm font-semibold tracking-wide text-slate-400">Basic details</p>
                <div className="grid gap-6 md:grid-cols-2">
                    <FormField label="Project name" required error={errors.name}>
                        <Input value={data.name} onChange={(event) => setData('name', event.target.value)} />
                    </FormField>
                    <FormField label="Domain" required hint="Root domain used for WordPress" error={errors.domain}>
                        <Input value={data.domain} onChange={(event) => setData('domain', event.target.value)} />
                    </FormField>
                    <FormField label="Container name" hint="Defaults to the domain slug" error={errors.container_name}>
                        <Input
                            value={data.container_name ?? ''}
                            onChange={(event) => setData('container_name', event.target.value)}
                        />
                    </FormField>
                    <FormField
                        label="Docker image"
                        required
                        hint="Customize to pin a specific WordPress image"
                        error={errors.docker_image}
                    >
                        <Input
                            value={data.docker_image}
                            onChange={(event) => setData('docker_image', event.target.value)}
                        />
                    </FormField>
                </div>
            </section>

            <section className="card space-y-4">
                <p className="text-sm font-semibold tracking-wide text-slate-400">Where to place it</p>
                <div className="grid gap-6 md:grid-cols-2">
                    <FormField label="Availability zone" required error={errors.availability_zone}>
                        <Select
                            value={data.availability_zone}
                            onChange={(event) => setData('availability_zone', event.target.value)}
                        >
                            <option value="">Select an AZ</option>
                            {azOptions.map((az) => (
                                <option key={az} value={az}>
                                    {az}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                    <FormField label="WordPress image" required error={errors.docker_image}>
                        <Select
                            value={data.docker_image}
                            onChange={(event) => setData('docker_image', event.target.value)}
                        >
                            {imageOptions.map((image) => (
                                <option key={image} value={image}>
                                    {image}
                                </option>
                            ))}
                        </Select>
                    </FormField>
                </div>
            </section>

            <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p className="text-sm text-slate-400">
                    We will push your configuration to the server and orchestrate Docker automatically.
                </p>
                <button
                    type="submit"
                    disabled={processing}
                    className="inline-flex items-center justify-center rounded-xl border border-sky-500/50 bg-sky-500/20 px-6 py-3 text-sm font-semibold text-sky-50 transition hover:bg-sky-500/30 disabled:cursor-not-allowed disabled:opacity-50"
                >
                    {processing ? 'Saving…' : site?.id ? 'Update site' : 'Provision site'}
                </button>
            </div>
        </form>
    );
}
