package com.example.crises; // Define the package this class belongs to

import android.content.Context; // Import Context to use with LayoutInflater for inflating item views
import android.view.LayoutInflater; // Import LayoutInflater to inflate each chat bubble XML layout
import android.view.View; // Import View as the base class for all UI elements
import android.view.ViewGroup; // Import ViewGroup as the parent container passed to onCreateViewHolder
import android.widget.FrameLayout; // Import FrameLayout for the avatar container view
import android.widget.RelativeLayout; // Import RelativeLayout.LayoutParams to dynamically align views left or right
import android.widget.TextView; // Import TextView for the message bubble and timestamp

import androidx.annotation.NonNull; // Import NonNull annotation for null-safety contracts
import androidx.recyclerview.widget.RecyclerView; // Import RecyclerView base classes for adapter and ViewHolder

import java.util.List; // Import List to hold the chat messages

public class ChatAdapter extends RecyclerView.Adapter<ChatAdapter.MessageViewHolder> { // Adapter for the chat RecyclerView; generic type is our own MessageViewHolder

    private final Context context; // Context needed to inflate item layouts
    private final List<ChatMessage> messages; // The list of chat messages to display

    public ChatAdapter(Context context, List<ChatMessage> messages) { // Constructor: receives context and the messages list
        this.context = context; // Store context for use in onCreateViewHolder
        this.messages = messages; // Store reference to the messages list
    }

    @NonNull
    @Override
    public MessageViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) { // Called when RecyclerView needs a new chat bubble view
        View view = LayoutInflater.from(context)
                .inflate(R.layout.item_message, parent, false); // Inflate item_message.xml (false = don't attach to parent yet)
        return new MessageViewHolder(view); // Wrap the inflated view in a MessageViewHolder and return it
    }

    @Override
    public void onBindViewHolder(@NonNull MessageViewHolder holder, int position) { // Called to bind a message's data to an existing ViewHolder
        ChatMessage msg = messages.get(position); // Get the message object at this position

        holder.tvMessage.setText(msg.getText()); // Set the message bubble text
        holder.tvTime.setText(msg.getTime()); // Set the timestamp text

        if (msg.getType() == ChatMessage.TYPE_USER) { // Check if this message was sent by the user

            holder.layoutAvatar.setVisibility(View.GONE); // Hide the AI avatar since this is a user message

            holder.tvMessage.setBackgroundResource(R.drawable.bubble_user_bg); // Apply the blue user bubble background
            holder.tvMessage.setTextColor(0xFFFFFFFF); // Set message text to white (0xFF = fully opaque, FFFFFF = white)

            RelativeLayout.LayoutParams msgParams =
                    (RelativeLayout.LayoutParams) holder.tvMessage.getLayoutParams(); // Get current layout params to modify alignment
            msgParams.addRule(RelativeLayout.ALIGN_PARENT_END); // Align message bubble to the right side
            msgParams.removeRule(RelativeLayout.ALIGN_PARENT_START); // Remove any left alignment from a previous bind
            holder.tvMessage.setLayoutParams(msgParams); // Apply the updated layout params

            RelativeLayout.LayoutParams timeParams =
                    (RelativeLayout.LayoutParams) holder.tvTime.getLayoutParams(); // Get timestamp layout params to modify alignment
            timeParams.addRule(RelativeLayout.ALIGN_PARENT_END); // Align timestamp to the right side
            timeParams.removeRule(RelativeLayout.ALIGN_PARENT_START); // Remove any left alignment from a previous bind
            holder.tvTime.setLayoutParams(timeParams); // Apply the updated timestamp layout params

        } else { // Otherwise this message is from the AI

            holder.layoutAvatar.setVisibility(View.VISIBLE); // Show the AI avatar next to the bubble

            holder.tvMessage.setBackgroundResource(R.drawable.bubble_ai_bg); // Apply the white AI bubble background
            holder.tvMessage.setTextColor(0xFF1A1A2E); // Set message text to dark navy (0xFF = fully opaque, 1A1A2E = dark navy)

            RelativeLayout.LayoutParams msgParams =
                    (RelativeLayout.LayoutParams) holder.tvMessage.getLayoutParams(); // Get current layout params to modify alignment
            msgParams.addRule(RelativeLayout.ALIGN_PARENT_START); // Align message bubble to the left side
            msgParams.removeRule(RelativeLayout.ALIGN_PARENT_END); // Remove any right alignment from a previous bind
            holder.tvMessage.setLayoutParams(msgParams); // Apply the updated layout params

            RelativeLayout.LayoutParams timeParams =
                    (RelativeLayout.LayoutParams) holder.tvTime.getLayoutParams(); // Get timestamp layout params to modify alignment
            timeParams.addRule(RelativeLayout.ALIGN_PARENT_START); // Align timestamp to the left side
            timeParams.removeRule(RelativeLayout.ALIGN_PARENT_END); // Remove any right alignment from a previous bind
            holder.tvTime.setLayoutParams(timeParams); // Apply the updated timestamp layout params
        }
    }

    @Override
    public int getItemCount() { // Tells RecyclerView how many items to display
        return messages.size(); // Return the total number of messages in the list
    }

    public static class MessageViewHolder extends RecyclerView.ViewHolder { // Static inner class holding references to each chat bubble's views

        FrameLayout layoutAvatar; // Container for the AI avatar icon (hidden for user messages)
        TextView tvMessage; // The chat bubble text view
        TextView tvTime; // The timestamp text view

        public MessageViewHolder(@NonNull View itemView) { // Constructor: receives the inflated item view
            super(itemView); // Pass itemView to RecyclerView.ViewHolder
            layoutAvatar = itemView.findViewById(R.id.layoutAvatar); // Find the avatar container
            tvMessage = itemView.findViewById(R.id.tvMessage); // Find the message bubble TextView
            tvTime = itemView.findViewById(R.id.tvTime); // Find the timestamp TextView
        }
    }
}