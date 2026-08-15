import { LayoutList, LayoutGrid, Calendar } from 'lucide-react';

export type ViewMode = 'list' | 'kanban' | 'calendar';

interface Props {
    value: ViewMode;
    onChange: (mode: ViewMode) => void;
    modes?: ViewMode[];
}

const ICONS: Record<ViewMode, any> = { list: LayoutList, kanban: LayoutGrid, calendar: Calendar };
const LABELS: Record<ViewMode, string> = { list: 'Liste', kanban: 'Kanban', calendar: 'Calendrier' };

export default function ViewSwitcher({ value, onChange, modes = ['list', 'kanban'] }: Props) {
    return (
        <div className="inline-flex rounded-lg border border-gray-300 dark:border-gray-600 overflow-hidden">
            {modes.map((m) => {
                const Icon = ICONS[m];
                return (
                    <button
                        key={m}
                        type="button"
                        title={LABELS[m]}
                        onClick={() => onChange(m)}
                        className={`flex items-center gap-1.5 px-3 py-2 text-sm font-medium transition ${
                            value === m
                                ? 'bg-brand-navy text-white'
                                : 'bg-white dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-600'
                        }`}
                    >
                        <Icon className="h-4 w-4" />
                        <span className="hidden sm:inline">{LABELS[m]}</span>
                    </button>
                );
            })}
        </div>
    );
}
