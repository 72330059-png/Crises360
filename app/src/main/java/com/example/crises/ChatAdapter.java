package com.example.crises;

import android.content.Context;
import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;
import android.widget.FrameLayout;
import android.widget.RelativeLayout;
import android.widget.TextView;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

public class ChatAdapter extends RecyclerView.Adapter<ChatAdapter.MessageViewHolder> {

    private final Context           context;
    private final List<ChatMessage> messages;

    public ChatAdapter(Context context, List<ChatMessage> messages) {
        this.context  = context;
        this.messages = messages;
    }

    @NonNull
    @Override
    public MessageViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context)
                .inflate(R.layout.item_message, parent, false);
        return new MessageViewHolder(view);
    }

    @Override
    public void onBindViewHolder(@NonNull MessageViewHolder holder, int position) {
        ChatMessage msg = messages.get(position);

        holder.tvMessage.setText(msg.getText());
        holder.tvTime.setText(msg.getTime());

        if (msg.getType() == ChatMessage.TYPE_USER) {

            // Hide avatar
            holder.layoutAvatar.setVisibility(View.GONE);

            // Purple bubble, white text
            holder.tvMessage.setBackgroundResource(R.drawable.bubble_user_bg);
            holder.tvMessage.setTextColor(0xFFFFFFFF);

            // Align message container to the right
            RelativeLayout.LayoutParams msgParams =
                    (RelativeLayout.LayoutParams) holder.tvMessage.getLayoutParams();
            msgParams.addRule(RelativeLayout.ALIGN_PARENT_END);
            msgParams.removeRule(RelativeLayout.ALIGN_PARENT_START);
            holder.tvMessage.setLayoutParams(msgParams);

            // Align time to the right
            RelativeLayout.LayoutParams timeParams =
                    (RelativeLayout.LayoutParams) holder.tvTime.getLayoutParams();
            timeParams.addRule(RelativeLayout.ALIGN_PARENT_END);
            timeParams.removeRule(RelativeLayout.ALIGN_PARENT_START);
            holder.tvTime.setLayoutParams(timeParams);

        } else {

            // Show avatar
            holder.layoutAvatar.setVisibility(View.VISIBLE);

            // White bubble, dark text
            holder.tvMessage.setBackgroundResource(R.drawable.bubble_ai_bg);
            holder.tvMessage.setTextColor(0xFF1A1A2E);

            // Align message container to the left
            RelativeLayout.LayoutParams msgParams =
                    (RelativeLayout.LayoutParams) holder.tvMessage.getLayoutParams();
            msgParams.addRule(RelativeLayout.ALIGN_PARENT_START);
            msgParams.removeRule(RelativeLayout.ALIGN_PARENT_END);
            holder.tvMessage.setLayoutParams(msgParams);

            // Align time to the left
            RelativeLayout.LayoutParams timeParams =
                    (RelativeLayout.LayoutParams) holder.tvTime.getLayoutParams();
            timeParams.addRule(RelativeLayout.ALIGN_PARENT_START);
            timeParams.removeRule(RelativeLayout.ALIGN_PARENT_END);
            holder.tvTime.setLayoutParams(timeParams);
        }
    }

    @Override
    public int getItemCount() {
        return messages.size();
    }

    // ----------------------------------------------------------------
    // ViewHolder
    // ----------------------------------------------------------------

    public static class MessageViewHolder extends RecyclerView.ViewHolder {

        FrameLayout layoutAvatar;
        TextView    tvMessage;
        TextView    tvTime;

        public MessageViewHolder(@NonNull View itemView) {
            super(itemView);
            layoutAvatar = itemView.findViewById(R.id.layoutAvatar);
            tvMessage    = itemView.findViewById(R.id.tvMessage);
            tvTime       = itemView.findViewById(R.id.tvTime);
        }
    }
}