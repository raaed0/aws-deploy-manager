import clsx from 'clsx';

export default function Select({ className = '', children, ...props }) {
    return (
        <select
            {...props}
            className={clsx(
                'w-full rounded-xl border border-slate-700 bg-slate-900/80 px-4 py-2 text-base text-white shadow-inner',
                'focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-500/40',
                className,
            )}
        >
            {children}
        </select>
    );
}
