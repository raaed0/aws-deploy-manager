import Input from './Input';

export default function EnvironmentFields({ value = [], onChange }) {
    const rows = value.length ? value : [{ key: '', value: '' }];

    const update = (index, field, fieldValue) => {
        const next = rows.map((row, idx) =>
            idx === index
                ? {
                      ...row,
                      [field]: fieldValue,
                  }
                : row,
        );

        onChange(next);
    };

    const addRow = () => onChange([...rows, { key: '', value: '' }]);
    const removeRow = (index) => onChange(rows.filter((_, idx) => idx !== index));

    return (
        <div className="space-y-3">
            {rows.map((pair, index) => (
                <div key={`env-${index}`} className="flex items-center gap-3">
                    <Input
                        value={pair.key}
                        placeholder="KEY"
                        className="uppercase"
                        onChange={(event) => update(index, 'key', event.target.value)}
                    />
                    <Input
                        value={pair.value}
                        placeholder="value"
                        onChange={(event) => update(index, 'value', event.target.value)}
                    />
                    <button
                        type="button"
                        className="rounded-xl border border-white/10 bg-slate-900/60 px-3 py-2 text-sm text-slate-300 hover:text-white"
                        onClick={() => removeRow(index)}
                    >
                        Remove
                    </button>
                </div>
            ))}

            <button
                type="button"
                className="rounded-xl border border-dashed border-slate-700 px-4 py-2 text-sm text-sky-300 hover:border-sky-500 hover:text-sky-200"
                onClick={addRow}
            >
                Add variable
            </button>
        </div>
    );
}
