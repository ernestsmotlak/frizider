import { onUnmounted, ref, type Ref } from "vue";

const ROUND_BTN_SIZE_PX = 40;
const LONG_PRESS_MS = 150;

export interface UseDraggableRoundButtonOptions {
    initialLeft?: number;
    initialTop?: number;
    onClick?: (position: { left: number; top: number }) => void;
    onDragEnd?: (position: { left: number; top: number }) => void;
}

export interface UseDraggableRoundButtonReturn {
    containerRef: Ref<HTMLElement | null>;
    roundButtonLeft: Ref<number>;
    roundButtonTop: Ref<number>;
    roundBtnDragging: Ref<boolean>;
    onRoundBtnPointerDown: (e: MouseEvent | TouchEvent) => void;
}

export function useDraggableRoundButton(
    options: UseDraggableRoundButtonOptions = {},
): UseDraggableRoundButtonReturn {
    const initialLeft = options.initialLeft ?? 18;
    const initialTop = options.initialTop ?? 52.5;

    const onClick = options.onClick;
    const onDragEnd = options.onDragEnd;
    const containerRef = ref<HTMLElement | null>(null);
    const roundButtonLeft = ref(initialLeft);
    const roundButtonTop = ref(initialTop);
    const roundBtnDragging = ref(false);
    const roundBtnOffset = ref({ x: 0, y: 0 });
    let longPressTimerId: ReturnType<typeof setTimeout> | null = null;

    function getCardRect(): DOMRect | null {
        return containerRef.value?.getBoundingClientRect() ?? null;
    }

    function clampRoundBtnPosition(
        left: number,
        top: number,
    ): { left: number; top: number } {
        const rect = getCardRect();
        if (!rect) return { left: roundButtonLeft.value, top: roundButtonTop.value };
        const maxLeft = Math.max(0, rect.width - ROUND_BTN_SIZE_PX);
        const maxTop = Math.max(0, rect.height - ROUND_BTN_SIZE_PX);
        return {
            left: Math.max(0, Math.min(maxLeft, left)),
            top: Math.max(0, Math.min(maxTop, top)),
        };
    }

    function applyRoundBtnPosition(clientX: number, clientY: number): void {
        const rect = getCardRect();
        if (!rect) return;
        const left = clientX - rect.left - roundBtnOffset.value.x;
        const top = clientY - rect.top - roundBtnOffset.value.y;
        const clamped = clampRoundBtnPosition(left, top);
        roundButtonLeft.value = clamped.left;
        roundButtonTop.value = clamped.top;
    }

    function onDocumentPointerMove(e: MouseEvent): void {
        if (!roundBtnDragging.value) return;
        applyRoundBtnPosition(e.clientX, e.clientY);
    }

    function onDocumentTouchMove(e: TouchEvent): void {
        if (!roundBtnDragging.value || e.touches.length === 0) return;
        e.preventDefault();
        applyRoundBtnPosition(e.touches[0].clientX, e.touches[0].clientY);
    }

    function onDocumentPointerUp(): void {
        const wasDragging = roundBtnDragging.value;
        roundBtnDragging.value = false;
        document.removeEventListener("mousemove", onDocumentPointerMove);
        document.removeEventListener("mouseup", onDocumentPointerUp);
        document.removeEventListener("touchmove", onDocumentTouchMove);
        document.removeEventListener("touchend", onDocumentPointerUp);
        if (wasDragging) {
            onDragEnd?.({ left: roundButtonLeft.value, top: roundButtonTop.value });
        }
    }

    function startDrag(downClientX: number, downClientY: number): void {
        const rect = getCardRect();
        if (!rect) return;
        roundBtnOffset.value = {
            x: downClientX - (rect.left + roundButtonLeft.value),
            y: downClientY - (rect.top + roundButtonTop.value),
        };
        roundBtnDragging.value = true;
        document.addEventListener("mousemove", onDocumentPointerMove);
        document.addEventListener("mouseup", onDocumentPointerUp);
        document.addEventListener("touchmove", onDocumentTouchMove, { passive: false });
        document.addEventListener("touchend", onDocumentPointerUp);
    }

    function clearLongPressTimer(): void {
        if (longPressTimerId != null) {
            clearTimeout(longPressTimerId);
            longPressTimerId = null;
        }
    }

    function onRoundBtnPointerDown(e: MouseEvent | TouchEvent): void {
        const clientX = "touches" in e ? e.touches[0].clientX : e.clientX;
        const clientY = "touches" in e ? e.touches[0].clientY : e.clientY;
        if ("touches" in e) e.preventDefault();
        const rect = getCardRect();
        if (!rect) return;

        const handlePointerUpWhileWaiting = (): void => {
            clearLongPressTimer();
            document.removeEventListener("mouseup", handlePointerUpWhileWaiting);
            document.removeEventListener("touchend", handlePointerUpWhileWaiting);
            if (!roundBtnDragging.value) {
                onClick?.({ left: roundButtonLeft.value, top: roundButtonTop.value });
            }
        };

        longPressTimerId = setTimeout(() => {
            longPressTimerId = null;
            document.removeEventListener("mouseup", handlePointerUpWhileWaiting);
            document.removeEventListener("touchend", handlePointerUpWhileWaiting);
            startDrag(clientX, clientY);
        }, LONG_PRESS_MS);

        document.addEventListener("mouseup", handlePointerUpWhileWaiting);
        document.addEventListener("touchend", handlePointerUpWhileWaiting);
    }

    onUnmounted(() => {
        clearLongPressTimer();
        document.removeEventListener("mousemove", onDocumentPointerMove);
        document.removeEventListener("mouseup", onDocumentPointerUp);
        document.removeEventListener("touchmove", onDocumentTouchMove);
        document.removeEventListener("touchend", onDocumentPointerUp);
    });

    return {
        containerRef,
        roundButtonLeft,
        roundButtonTop,
        roundBtnDragging,
        onRoundBtnPointerDown,
    };
}
