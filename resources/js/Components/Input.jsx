import clsx from 'clsx';

export default function Input({ className = '', ...props }) {
    return (
        <input
            {...props}
            className={clsx(
                'w-full rounded-xl border border-white/10 bg-white/5 px-4 py-2 text-base text-white placeholder:text-slate-500 focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-500/40',
                className,
            )}
        />
    );
}
