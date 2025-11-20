import { Transition } from '@headlessui/react';
import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';

export default function FlashBanner() {
    const { flash } = usePage().props;
    const [visible, setVisible] = useState(true);

    useEffect(() => {
        if (flash?.banner || flash?.error) {
            setVisible(true);
            const timer = setTimeout(() => setVisible(false), 5000);
            return () => clearTimeout(timer);
        }

        return undefined;
    }, [flash?.banner, flash?.error]);

    if (!flash?.banner && !flash?.error) {
        return null;
    }

    const color = flash.error ? 'bg-rose-500/10 text-rose-200 ring-rose-500/40' : 'bg-emerald-500/10 text-emerald-200 ring-emerald-500/40';

    return (
        <Transition
            show={visible}
            enter="transition duration-100 ease-out"
            enterFrom="opacity-0 -translate-y-2"
            enterTo="opacity-100 translate-y-0"
            leave="transition duration-150 ease-in"
            leaveFrom="opacity-100 translate-y-0"
            leaveTo="opacity-0 -translate-y-1"
        >
            <div className="border-b border-white/5 bg-slate-900/70 backdrop-blur">
                <div className="mx-auto max-w-5xl px-6 py-3">
                    <div
                        className={`flex items-center justify-between rounded-xl border border-white/10 px-4 py-3 text-sm ${color}`}
                    >
                        <p>{flash.error ?? flash.banner}</p>
                        <button
                            type="button"
                            className="text-xs uppercase tracking-wide text-white/50 hover:text-white"
                            onClick={() => setVisible(false)}
                        >
                            Dismiss
                        </button>
                    </div>
                </div>
            </div>
        </Transition>
    );
}
