package pt.ipleiria.estg.dei.amsi.cinelive.adapters;

import android.graphics.Bitmap;
import android.view.LayoutInflater;
import android.view.ViewGroup;

import androidx.annotation.NonNull;
import androidx.recyclerview.widget.RecyclerView;

import java.util.List;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ItemBilheteBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.models.Bilhete;
import pt.ipleiria.estg.dei.amsi.cinelive.utils.QRCodeUtils;

public class BilhetesAdapter extends RecyclerView.Adapter<BilhetesAdapter.ViewHolder> {
    private final List<Bilhete> bilhetes;

    public BilhetesAdapter(List<Bilhete> bilhetes) {
        this.bilhetes = bilhetes;
    }

    @NonNull
    @Override
    public ViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {

        // Inflate do layout de um item (item_bilhete.xml)
        ItemBilheteBinding binding = ItemBilheteBinding.inflate(
                LayoutInflater.from(parent.getContext()), parent, false
        );
        return new ViewHolder(binding);
    }

    @Override
    public void onBindViewHolder(@NonNull ViewHolder holder, int position) {
        Bilhete bilhete = bilhetes.get(position);

        // Preencher campos com os dados do bilhete
        holder.binding.tvCodigo.setText(bilhete.getCodigo());
        holder.binding.tvLugar.setText(bilhete.getLugar());
        holder.binding.tvPreco.setText(bilhete.getPreco());
        holder.binding.tvEstado.setText(bilhete.getEstado());

        // Criar o QR Code
        Bitmap qr = QRCodeUtils.generate(bilhete.getCodigo());
        holder.binding.ivQrCode.setImageBitmap(qr);
    }

    @Override
    public int getItemCount() {
        return bilhetes.size();
    }

    // O ViewHolder representa 1 item da lista
    static class ViewHolder extends RecyclerView.ViewHolder {
        ItemBilheteBinding binding;

        public ViewHolder(ItemBilheteBinding binding) {
            super(binding.getRoot());
            this.binding = binding;
        }
    }
}
