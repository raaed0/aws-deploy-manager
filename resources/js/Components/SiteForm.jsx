import { useForm } from '@inertiajs/react';
import FormField from './FormField';
import Input from './Input';
import Select from './Select';
import Textarea from './Textarea';
import EnvironmentFields from './EnvironmentFields';

const defaultValues = {
    name: '',
    domain: '',
    container_name: '',
    server_host: '',
    server_port: 22,
    server_user: 'root',
    auth_type: 'key',
    server_password: '',
    server_private_key: '',
    docker_image: 'wordpress:latest',
    database_name: '',
    database_username: '',
    database_password: '',
    environment: [],
};

const normalizeEnvironment = (value) => {
    if (!value) {
        return [];
    }

    if (Array.isArray(value)) {
        return value;
    }

    return Object.entries(value).map(([key, val]) => ({ key, value: val }));
};

export default function SiteForm({ site = null }) {
    const initialData = { ...defaultValues, ...site, environment: normalizeEnvironment(site?.environment) };
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
                <p className="text-sm font-semibold tracking-wide text-slate-400">Server access</p>
                <div className="grid gap-6 md:grid-cols-2">
                    <FormField label="Server host / IP" required error={errors.server_host}>
                        <Input
                            value={data.server_host}
                            placeholder="203.0.113.10"
                            onChange={(event) => setData('server_host', event.target.value)}
                        />
                    </FormField>
                    <FormField label="SSH Port" required error={errors.server_port}>
                        <Input
                            type="number"
                            value={data.server_port}
                            onChange={(event) => setData('server_port', event.target.value)}
                        />
                    </FormField>
                    <FormField label="Username" required error={errors.server_user}>
                        <Input value={data.server_user} onChange={(event) => setData('server_user', event.target.value)} />
                    </FormField>
                    <FormField label="Authentication" required error={errors.auth_type}>
                        <Select value={data.auth_type} onChange={(event) => setData('auth_type', event.target.value)}>
                            <option value="key">SSH key</option>
                            <option value="password">Password</option>
                        </Select>
                    </FormField>
                    {data.auth_type === 'password' ? (
                        <FormField label="SSH Password" required error={errors.server_password}>
                            <Input
                                type="password"
                                value={data.server_password ?? ''}
                                onChange={(event) => setData('server_password', event.target.value)}
                            />
                        </FormField>
                    ) : (
                        <FormField
                            label="Private key"
                            required
                            error={errors.server_private_key}
                            className="md:col-span-2"
                        >
                            <Textarea
                                rows={6}
                                value={data.server_private_key ?? ''}
                                onChange={(event) => setData('server_private_key', event.target.value)}
                            />
                        </FormField>
                    )}
                </div>
            </section>

            <section className="card space-y-4">
                <p className="text-sm font-semibold tracking-wide text-slate-400">Database credentials</p>
                <div className="grid gap-6 md:grid-cols-3">
                    <FormField label="Database name" required error={errors.database_name}>
                        <Input
                            value={data.database_name}
                            onChange={(event) => setData('database_name', event.target.value)}
                        />
                    </FormField>
                    <FormField label="Database user" required error={errors.database_username}>
                        <Input
                            value={data.database_username}
                            onChange={(event) => setData('database_username', event.target.value)}
                        />
                    </FormField>
                    <FormField label="Database password" required error={errors.database_password}>
                        <Input
                            type="password"
                            value={data.database_password}
                            onChange={(event) => setData('database_password', event.target.value)}
                        />
                    </FormField>
                </div>
            </section>

            <section className="card space-y-4">
                <div className="flex items-center justify-between">
                    <div>
                        <p className="text-sm font-semibold tracking-wide text-slate-400">Environment overrides</p>
                        <p className="text-xs text-slate-500">
                            Custom WP constants and Docker environment variables.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setData('environment', [])}
                        className="text-xs uppercase tracking-wide text-slate-400 hover:text-white"
                    >
                        Clear
                    </button>
                </div>
                <EnvironmentFields value={data.environment} onChange={(value) => setData('environment', value)} />
                {errors.environment ? (
                    <p className="text-xs font-medium text-rose-300">{errors.environment}</p>
                ) : null}
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
