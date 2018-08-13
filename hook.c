#include <X11/Xlib.h>
#include <X11/Xutil.h>
 
#include <cstdio>
 
int grabKey(Display * display, Window window, KeyCode keycode)
{
    // numlock on
    unsigned int modifiers = Mod2Mask;
    Bool ownerEvents  = True;
    int  pointerMode  = GrabModeAsync;
    int  keyboardMode = GrabModeAsync;
 
    XGrabKey(display, keycode, modifiers, window, ownerEvents, pointerMode, keyboardMode);
    return keycode;
}
 
void ungrabKey(Display * display, Window window, KeyCode keycode)
{
    // numlock on
    unsigned int modifiers = Mod2Mask;
 
    XUngrabKey(display, keycode, modifiers, window);
}
 
int main()
{
    Display* display = XOpenDisplay(0);
    Window   root    = DefaultRootWindow(display);
    XEvent   event;
 
    KeyCode keyA = XKeysymToKeycode(display, 'a');
    KeyCode keyB = XKeysymToKeycode(display, 's');
    KeyCode keyC = XKeysymToKeycode(display, 'd');
    KeyCode keyX = XKeysymToKeycode(display, 'x');
 
    grabKey(display, root, keyA);
    grabKey(display, root, keyB);
    grabKey(display, root, keyC);
    grabKey(display, root, keyX);
 
    XSelectInput(display, root, KeyPressMask);
 
    while(true)
    {
        XNextEvent(display, &event);
        if(event.type == KeyPress)
        {
            std::printf("key pressed: %d\n", event.xkey.keycode);
 
            if(event.xkey.keycode == keyX)
            {
                break;
            }
        }
    }
 
    ungrabKey(display, root, keyX);
    ungrabKey(display, root, keyC);
    ungrabKey(display, root, keyB);
    ungrabKey(display, root, keyA);
 
    XCloseDisplay(display);
}
