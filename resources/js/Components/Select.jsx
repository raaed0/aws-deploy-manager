import clsx from 'clsx';

export default function Select({ className = '', children, ...props }) {
    return (
        <select
            {...props}
            className={clsx(
                'w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-base text-white focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-500/40',
                className,
            )}
        >
            {children}
        </select>
    );
}
