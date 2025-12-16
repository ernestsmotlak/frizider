export const formatTime = (minutes: number | null): string => {
    if (minutes === null || minutes === undefined) return "N/A";
    if (minutes < 60) return `${minutes} min`;

    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;

    return mins > 0 ? `${hours}h ${mins}min` : `${hours}h`;
};
